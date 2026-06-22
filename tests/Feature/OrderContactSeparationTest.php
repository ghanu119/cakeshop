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

class OrderContactSeparationTest extends TestCase
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

    public function test_admin_order_show_displays_contact_and_account_sections(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);

        $order = Order::factory()
            ->for(Product::factory()->create(['status' => 'active']))
            ->create([
                'user_id' => $customer->id,
                'guest_name' => 'Priya Sharma',
                'guest_email' => 'priya@example.com',
                'guest_phone' => '9999888777',
            ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order));

        $response->assertOk()
            ->assertSee(__('Contact for this order'), false)
            ->assertSee(__('Linked account'), false)
            ->assertSee(__('Reveal detail'), false)
            ->assertSee('data-linked-account-toggle', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('Priya Sharma', false)
            ->assertSee('priya@example.com', false);
    }

    public function test_order_confirm_shows_order_contact(): void
    {
        $order = Order::factory()
            ->for(Product::factory()->create(['status' => 'active']))
            ->create([
                'guest_name' => 'Priya Sharma',
                'guest_email' => 'priya@example.com',
                'guest_phone' => '9999888777',
            ]);

        $this->get(route('order.confirm', $order))
            ->assertOk()
            ->assertSee(__('Order contact'), false)
            ->assertSee('Priya Sharma', false)
            ->assertSee('priya@example.com', false);
    }

    public function test_kitchen_order_views_hide_guest_and_account_contact(): void
    {
        $kitchen = User::factory()->create(['email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);

        $todayOrder = Order::factory()
            ->verified()
            ->for(Product::factory()->create(['status' => 'active']))
            ->create([
                'user_id' => $customer->id,
                'order_status' => 'processing',
                'guest_name' => 'Priya Sharma',
                'guest_email' => 'priya@example.com',
                'guest_phone' => '9999888777',
                'delivery_at' => Carbon::now('Asia/Kolkata')->addHours(6)->utc(),
                'preparation_at' => Carbon::now('UTC')->addHour(),
            ]);

        $upcomingOrder = Order::factory()
            ->verified()
            ->for(Product::factory()->create(['status' => 'active']))
            ->create([
                'user_id' => $customer->id,
                'guest_name' => 'Priya Sharma',
                'guest_email' => 'priya@example.com',
                'guest_phone' => '9999888777',
                'delivery_at' => Carbon::now('Asia/Kolkata')->addDay()->addHours(4)->utc(),
            ]);

        $this->actingAs($kitchen)->get(route('admin.kitchen.orders.index'))
            ->assertOk()
            ->assertDontSee('Priya Sharma', false)
            ->assertDontSee('9999888777', false)
            ->assertDontSee(__('Guest'), false);

        $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming'))
            ->assertOk()
            ->assertDontSee('Priya Sharma', false)
            ->assertDontSee('9999888777', false)
            ->assertDontSee(__('Guest'), false);

        $this->actingAs($kitchen)->get(route('admin.kitchen.orders.show', $todayOrder))
            ->assertOk()
            ->assertDontSee(__('Contact for this order'), false)
            ->assertDontSee('Priya Sharma', false)
            ->assertDontSee('priya@example.com', false)
            ->assertDontSee('9999888777', false)
            ->assertDontSee(__('Linked account'), false)
            ->assertDontSee('Roanna Jefferson', false);

        $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming.show', $upcomingOrder))
            ->assertOk()
            ->assertDontSee(__('Contact for this order'), false)
            ->assertDontSee('Priya Sharma', false)
            ->assertDontSee('priya@example.com', false)
            ->assertDontSee('9999888777', false)
            ->assertDontSee(__('Linked account'), false)
            ->assertDontSee('Roanna Jefferson', false);
    }
}
