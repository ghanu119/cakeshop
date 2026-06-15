<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountCheckoutTest extends TestCase
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

    private function orderPayload(): array
    {
        return [
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => 'takeaway',
        ];
    }

    public function test_guest_is_redirected_to_login_when_placing_order(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->get(route('order.place', $product))
            ->assertRedirect(route('account.login', ['intended' => route('order.place', $product)]));
    }

    public function test_logged_in_customer_can_access_order_form(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['status' => 'active']);

        $this->actingAs($customer)
            ->get(route('order.place', $product))
            ->assertOk();
    }

    public function test_order_form_prefills_customer_contact_details(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->actingAs($customer)->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('value="Roanna Jefferson"', false);
        $response->assertSee('value="8282938816"', false);
        $response->assertSee('value="roanna@example.com"', false);
        $response->assertSee(__('Clear'), false);
    }

    public function test_customer_can_view_order_details_from_account(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['status' => 'active', 'price' => 449]);
        $order = Order::factory()
            ->for($product)
            ->create([
                'user_id' => $customer->id,
                'guest_name' => $customer->name,
                'guest_phone' => $customer->phone,
                'guest_email' => $customer->email,
                'amount' => 449,
            ]);

        $this->actingAs($customer)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee(route('account.orders.show', $order), false)
            ->assertSee(__('View details'), false);

        $this->actingAs($customer)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_no, false)
            ->assertSee(__('Order details'), false)
            ->assertSee(__('Payment required'), false)
            ->assertSee(__('My orders'), false)
            ->assertSee(__('Back to account'), false);
    }

    public function test_logged_in_customer_sees_account_nav_on_order_confirm(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()
            ->for(Product::factory()->create(['status' => 'active']))
            ->create(['user_id' => $customer->id]);

        $this->actingAs($customer)
            ->get(route('order.confirm', $order))
            ->assertOk()
            ->assertSee(__('My orders'), false)
            ->assertSee(__('Back to account'), false)
            ->assertDontSee(__('Look up your order'), false);
    }

    public function test_customer_order_sets_user_id(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '9876501234',
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->actingAs($customer)
            ->post(route('order.store', $product), array_merge($this->orderPayload(), [
                'guest_name' => $customer->name,
                'guest_phone' => $customer->phone,
                'guest_email' => $customer->email,
            ]))
            ->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Buyer', $order->guest_name);
    }
}
