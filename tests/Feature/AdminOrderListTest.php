<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::flushCache();
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_admin_can_sort_orders_by_amount_descending(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        $low = Order::factory()->for($product)->create([
            'order_no' => 'ORD-LOW-001',
            'amount' => 100,
        ]);
        $high = Order::factory()->for($product)->create([
            'order_no' => 'ORD-HIGH-001',
            'amount' => 900,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'sort' => 'amount',
            'direction' => 'desc',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder([$high->order_no, $low->order_no]);
    }

    public function test_admin_order_list_paginates_results(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        Order::factory()->count(16)->for($product)->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSee('page=2', false);
        $response->assertSee(__('Showing :from–:to of :total orders', [
            'from' => 1,
            'to' => 15,
            'total' => 16,
        ]), false);
    }

    public function test_admin_order_list_shows_actions_first_and_delivery_at_column(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();
        $deliveryAt = Carbon::parse('2026-06-15 10:30', 'Asia/Kolkata')->utc();

        $order = Order::factory()->for($product)->create([
            'delivery_at' => $deliveryAt,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSeeInOrder([__('Actions'), __('Delivery at'), __('Order no')]);
        $response->assertSee($deliveryAt->setTimezone('Asia/Kolkata')->format('d M Y'));
        $response->assertSee($deliveryAt->setTimezone('Asia/Kolkata')->format('H:i'));
    }

    public function test_admin_can_filter_in_store_orders(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        $inStore = Order::factory()->for($product)->verified()->create([
            'order_no' => 'ORD-INSTORE-001',
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'placed_by_user_id' => $admin->id,
        ]);

        Order::factory()->for($product)->verified()->create([
            'order_no' => 'ORD-UPI-001',
            'payment_method' => Order::PAYMENT_METHOD_UPI,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'payment_status' => Order::PAYMENT_METHOD_CASH_ON_STORE,
        ]));

        $response->assertOk()
            ->assertSee($inStore->order_no, false)
            ->assertDontSee('ORD-UPI-001', false);
    }

    public function test_admin_order_list_shows_in_store_payment_stats_for_filtered_results(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        Order::factory()->for($product)->create([
            'order_no' => 'ORD-DUE-001',
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'payment_status' => Order::PAYMENT_STATUS_VERIFIED,
            'payment_amount' => 500,
            'amount' => 854.10,
            'placed_by_user_id' => $admin->id,
        ]);

        Order::factory()->for($product)->create([
            'order_no' => 'ORD-DUE-002',
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'payment_status' => Order::PAYMENT_STATUS_VERIFIED,
            'payment_amount' => 100,
            'amount' => 449.00,
            'placed_by_user_id' => $admin->id,
        ]);

        Order::factory()->for($product)->verified()->create([
            'order_no' => 'ORD-PAID-001',
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'payment_amount' => 700,
            'amount' => 700,
            'placed_by_user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'payment_status' => 'in_store_outstanding',
        ]));

        $response->assertOk()
            ->assertSee(__('Total'), false)
            ->assertSee('₹1,303.10', false)
            ->assertSee(__('Received'), false)
            ->assertSee('₹600.00', false)
            ->assertSee(__('Remaining'), false)
            ->assertSee('₹703.10', false)
            ->assertSee('ORD-DUE-001', false)
            ->assertSee('ORD-DUE-002', false)
            ->assertDontSee('ORD-PAID-001', false);
    }

    public function test_admin_order_list_payment_stats_include_online_and_pending_totals(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        Order::factory()->for($product)->verified()->create([
            'order_no' => 'ORD-ONLINE-001',
            'payment_method' => Order::PAYMENT_METHOD_UPI,
            'payment_amount' => 1200,
            'amount' => 1200,
            'placed_by_user_id' => null,
        ]);

        Order::factory()->for($product)->create([
            'order_no' => 'ORD-PENDING-001',
            'payment_method' => Order::PAYMENT_METHOD_UPI,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'amount' => 500,
            'placed_by_user_id' => null,
        ]);

        Order::factory()->for($product)->create([
            'order_no' => 'ORD-CASH-001',
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'payment_status' => Order::PAYMENT_STATUS_VERIFIED,
            'payment_amount' => 300,
            'amount' => 800,
            'placed_by_user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk()
            ->assertSee(__('Total'), false)
            ->assertSee('₹2,500.00', false)
            ->assertSee('₹1,500.00', false)
            ->assertSee('₹1,000.00', false);
    }
}
