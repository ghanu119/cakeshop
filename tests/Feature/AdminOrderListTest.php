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
}
