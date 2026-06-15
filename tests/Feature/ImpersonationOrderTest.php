<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\CustomerContext;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::flushCache();
    }

    private function validDeliveryAt(): string
    {
        $rules = app(OrderService::class)->deliveryAtRules();

        return Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');
    }

    private function orderPayload(User $customer): array
    {
        return [
            'guest_name' => $customer->name,
            'guest_phone' => $customer->phone,
            'guest_email' => $customer->email,
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => 'takeaway',
        ];
    }

    public function test_admin_impersonation_places_cash_on_store_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = User::factory()->customer()->create([
            'name' => 'Impersonated',
            'phone' => '9876000000',
            'email' => null,
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);

        $product = Product::factory()->create(['status' => 'active', 'price' => 750]);

        $this->actingAs($admin)
            ->post(route('admin.customers.impersonate', $customer))
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->post(route('order.store', $product), $this->orderPayload($customer))
            ->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('cash_on_store', $order->payment_method);
        $this->assertSame('verified', $order->payment_status);
        $this->assertSame($admin->id, $order->placed_by_user_id);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee(__('Cash on store — collected'), false)
            ->assertSee(__('Cash collected in store'), false)
            ->assertSee(__('Order source'), false)
            ->assertSee($admin->name, false)
            ->assertDontSee(__('No payment details submitted.'), false);
    }

    public function test_impersonation_banner_renders_in_document_flow_on_sticky_header_theme(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = User::factory()->customer()->create(['name' => 'Mem']);

        $product = Product::factory()->create(['status' => 'active', 'slug' => 'black-forest-cake']);

        $this->actingAs($admin)
            ->post(route('admin.customers.impersonate', $customer));

        $response = $this->actingAs($admin)
            ->get(route('products.show', $product->slug));

        $response->assertOk()
            ->assertSee(__('Ordering for :name (on behalf of customer)', ['name' => 'Mem']), false)
            ->assertSee(__('Ordering as :name', ['name' => 'Mem']), false)
            ->assertSee(__('Admin'), false)
            ->assertDontSee('fixed top-20', false);
    }

    public function test_stop_impersonation_clears_session(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $customer = User::factory()->customer()->create();

        $this->actingAs($admin)
            ->post(route('admin.customers.impersonate', $customer));

        $this->actingAs($admin)
            ->post(route('admin.impersonation.stop'))
            ->assertRedirect(route('admin.customers.show', $customer));

        $this->assertFalse(app(CustomerContext::class)->isImpersonating());
    }
}
