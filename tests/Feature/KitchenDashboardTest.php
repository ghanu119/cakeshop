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

class KitchenDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('kitchen_lead_hours', '');
        Setting::flushCache();
    }

    public function test_verified_future_order_appears_on_upcoming_not_today_index(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderTomorrow();

        $todayResponse = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.index'));
        $todayResponse->assertOk();
        $todayResponse->assertDontSee($order->order_no, false);

        $upcomingResponse = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming'));
        $upcomingResponse->assertOk();
        $upcomingResponse->assertSee($order->order_no, false);
    }

    public function test_unverified_future_order_not_on_upcoming(): void
    {
        $kitchen = $this->kitchenUser();
        $order = Order::factory()
            ->for(Product::factory())
            ->create([
                'payment_status' => 'pending',
                'delivery_at' => Carbon::now('Asia/Kolkata')->addDays(2)->utc(),
            ]);

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming'));

        $response->assertOk();
        $response->assertDontSee($order->order_no, false);
    }

    public function test_completed_verified_future_order_appears_on_upcoming(): void
    {
        $kitchen = $this->kitchenUser();
        $order = Order::factory()
            ->verified()
            ->completed()
            ->for(Product::factory())
            ->create([
                'delivery_at' => Carbon::now('Asia/Kolkata')->addDays(3)->utc(),
            ]);

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming'));

        $response->assertOk();
        $response->assertSee($order->order_no, false);
    }

    public function test_kitchen_upcoming_show_is_read_only(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderTomorrow();

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.upcoming.show', $order));

        $response->assertOk();
        $response->assertSee(__('Read-only — status can be updated on the delivery day when the order is set to Processing.'), false);
        $response->assertDontSee(__('Scheduled Delivery'), false);
        $response->assertDontSee(__('Delivery at'), false);
        $response->assertDontSee('data-order-status-form', false);
        $response->assertDontSee('name="order_status"', false);
    }

    public function test_kitchen_cannot_update_status_on_upcoming_order(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderTomorrow();

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);

        $response->assertNotFound();
        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_upcoming_preview_shows_days_left_highlight(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderTomorrow();

        $response = $this->actingAs($kitchen)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee(__('1 day left'), false);
        $response->assertSee($order->displayProductName(), false);
    }

    public function test_kitchen_dashboard_shows_today_and_upcoming_sections(): void
    {
        $kitchen = $this->kitchenUser();
        $admin = $this->adminUser();
        $todayOrder = $this->verifiedOrderToday();
        $upcomingOrder = $this->verifiedOrderTomorrow();

        $prepAt = $this->validPreparationAt($todayOrder);
        $this->actingAs($admin)->post(route('admin.orders.update-status', $todayOrder), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response = $this->actingAs($kitchen)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText(__("Today's Orders"));
        $response->assertSeeText(__('Upcoming'));
        $response->assertSee($todayOrder->displayProductName(), false);
        $response->assertSee($upcomingOrder->displayProductName(), false);
        $response->assertSee(route('admin.kitchen.orders.upcoming'), false);
    }

    public function test_upcoming_preview_respects_limit_of_six(): void
    {
        $kitchen = $this->kitchenUser();
        $product = Product::factory()->create();
        $tz = 'Asia/Kolkata';

        for ($i = 1; $i <= 8; $i++) {
            Order::factory()
                ->verified()
                ->for($product)
                ->create([
                    'delivery_at' => Carbon::now($tz)->addDays($i)->utc(),
                ]);
        }

        $preview = app(OrderService::class)->listKitchenUpcomingPreview();
        $this->assertCount(OrderService::KITCHEN_UPCOMING_PREVIEW_LIMIT, $preview);

        $response = $this->actingAs($kitchen)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee(__('+:count more orders', ['count' => 2]), false);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    private function kitchenUser(): User
    {
        $kitchen = User::factory()->create(['email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        return $kitchen;
    }

    private function verifiedOrderToday(): Order
    {
        $tz = 'Asia/Kolkata';
        $product = Product::factory()->create();
        $now = Carbon::now($tz);
        $deliveryAt = $now->copy()->addHours(6);

        if (! $deliveryAt->isSameDay($now)) {
            $deliveryAt = $now->copy()->endOfDay()->subHours(2);
        }

        return Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => $deliveryAt->utc(),
            ]);
    }

    private function verifiedOrderTomorrow(): Order
    {
        $product = Product::factory()->create();

        return Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => Carbon::now('Asia/Kolkata')->addDay()->addHours(4)->utc(),
            ]);
    }

    private function validPreparationAt(Order $order): string
    {
        $tz = 'Asia/Kolkata';
        $now = Carbon::now($tz);
        $delivery = $order->delivery_at->copy()->setTimezone($tz);
        $prep = $now->copy()->addHours(2);

        if ($prep->gte($delivery)) {
            $prep = $delivery->copy()->subHour();
        }

        return $prep->format('Y-m-d\TH:i');
    }
}
