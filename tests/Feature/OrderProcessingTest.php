<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('kitchen_lead_hours', '');
        Setting::flushCache();

        Mail::fake();
    }

    public function test_processing_status_requires_preparation_at(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday();

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
        ]);

        $response->assertSessionHasErrors('preparation_at');
        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_preparation_at_must_be_on_or_before_delivery(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday();
        $tz = 'Asia/Kolkata';
        $delivery = $order->delivery_at->copy()->setTimezone($tz);
        $tooLate = $delivery->copy()->addHour()->format('Y-m-d\TH:i');

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $tooLate,
        ]);

        $response->assertSessionHasErrors('preparation_at');
    }

    public function test_admin_can_process_order_after_delivery_time_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-06-22 18:00:00', 'Asia/Kolkata'));

        $admin = $this->adminUser();
        $tz = 'Asia/Kolkata';
        $product = Product::factory()->create();
        $order = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => Carbon::parse('2025-06-22 14:00:00', $tz)->utc(),
                'order_status' => 'pending',
            ]);

        $prepAt = Carbon::now($tz)->format('Y-m-d\TH:i');

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame('processing', $order->order_status);
        $this->assertNotNull($order->preparation_at);

        $todayOrdersResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));
        $todayOrdersResponse->assertOk();
        $todayOrdersResponse->assertSee($order->order_no, false);
    }

    public function test_kitchen_can_complete_overdue_processing_order(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-06-22 18:00:00', 'Asia/Kolkata'));
        Setting::set('kitchen_lead_hours', '4');
        Setting::flushCache();

        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $tz = 'Asia/Kolkata';
        $product = Product::factory()->create();
        $order = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => Carbon::parse('2025-06-22 14:00:00', $tz)->utc(),
                'order_status' => 'processing',
                'preparation_at' => Carbon::parse('2025-06-22 14:00:00', $tz)->utc(),
            ]);

        $this->assertTrue($order->canKitchenUpdateStatus());

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);

        $response->assertRedirect(route('admin.kitchen.orders.index'));
        $this->assertSame('completed', $order->fresh()->order_status);
    }

    public function test_processing_order_with_prep_appears_on_kitchen_index(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));

        $response->assertOk();
        $response->assertSee($order->order_no, false);

        $kitchenResponse = $this->actingAs($this->kitchenUser())->get(route('admin.kitchen.orders.index'));
        $kitchenResponse->assertOk();
        $kitchenResponse->assertSee($order->order_no, false);
    }

    public function test_pending_order_with_today_delivery_appears_on_kitchen_index_but_not_actionable(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();

        $indexResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));
        $indexResponse->assertOk();
        $indexResponse->assertSee($order->order_no, false);

        $showResponse = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.show', $order));
        $showResponse->assertOk();
        $showResponse->assertSee(__('Awaiting administrator — status can be updated after Processing is set with a preparation time.'), false);

        $updateResponse = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);
        $updateResponse->assertRedirect(route('admin.kitchen.orders.show', $order));
        $updateResponse->assertSessionHasErrors('_form');
        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_verified_today_order_without_prep_time_visible_but_not_actionable(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $order->order_status = 'processing';
        $order->preparation_at = null;
        $order->save();

        $this->assertTrue($order->fresh()->isAwaitingKitchenSetup());
        $this->assertFalse($order->fresh()->canKitchenUpdateStatus());

        $this->actingAs($kitchen)->get(route('admin.kitchen.orders.index'))
            ->assertOk()
            ->assertSee($order->order_no, false);

        $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ])
            ->assertRedirect(route('admin.kitchen.orders.show', $order))
            ->assertSessionHasErrors('_form');

        $this->assertSame('processing', $order->fresh()->order_status);
    }

    public function test_processing_order_with_tomorrow_delivery_not_on_kitchen_index(): void
    {
        $admin = $this->adminUser();
        $tz = 'Asia/Kolkata';
        $product = Product::factory()->create();
        $order = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'delivery_at' => Carbon::now($tz)->addDay()->addHours(4)->utc(),
            ]);

        $prepAt = Carbon::now($tz)->addHours(2)->format('Y-m-d\TH:i');

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ])->assertRedirect();

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));

        $response->assertOk();
        $response->assertDontSee($order->order_no, false);

        $upcomingResponse = $this->actingAs($admin)->get(route('admin.kitchen.orders.upcoming'));
        $upcomingResponse->assertOk();
        $upcomingResponse->assertSee($order->order_no, false);
    }

    public function test_kitchen_user_cannot_set_processing_or_preparation_time(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $admin = $this->adminUser();
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response->assertSessionHasErrors('order_status');
        $this->assertTrue($order->fresh()->isProcessing());
        $this->assertNotNull($order->fresh()->preparation_at);
    }

    public function test_kitchen_user_can_mark_order_completed_without_changing_preparation_time(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $admin = $this->adminUser();
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);

        $response->assertRedirect(route('admin.kitchen.orders.index'));
        $response->assertSessionHas('status');
        $order->refresh();
        $this->assertSame('completed', $order->order_status);
        $this->assertNull($order->preparation_at);
    }

    public function test_kitchen_user_redirected_to_index_after_cancelling_order(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $admin = $this->adminUser();
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'cancelled',
        ]);

        $response->assertRedirect(route('admin.kitchen.orders.index'));
        $this->assertSame('cancelled', $order->fresh()->order_status);
    }

    public function test_kitchen_cannot_view_completed_order_on_show_route(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $admin = $this->adminUser();
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.show', $order));

        $response->assertNotFound();
    }

    public function test_kitchen_order_show_hides_prepare_by_input(): void
    {
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $admin = $this->adminUser();
        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $response = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.show', $order));

        $response->assertOk();
        $response->assertDontSee('name="preparation_at"', false);
        $response->assertSee(__('Preparation time is set by an administrator.'), false);
        $response->assertDontSee(__('Scheduled Delivery'), false);
    }

    public function test_completed_status_clears_preparation_at_and_removes_from_kitchen(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday();
        $prepAt = $this->validPreparationAt($order);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $prepAt,
        ]);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'completed',
        ])->assertRedirect();

        $order->refresh();
        $this->assertNull($order->preparation_at);
        $this->assertSame('completed', $order->order_status);

        $kitchenResponse = $this->actingAs($this->kitchenUser())->get(route('admin.kitchen.orders.index'));
        $kitchenResponse->assertDontSee($order->order_no, false);

        $adminResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));
        $adminResponse->assertOk();
        $adminResponse->assertSee($order->order_no, false);
    }

    public function test_admin_kitchen_orders_index_redirects_to_admin_orders_today(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.kitchen.orders.index'));

        $response->assertRedirect(route('admin.orders.index', ['view' => 'today']));
    }

    public function test_admin_kitchen_orders_show_redirects_to_admin_order_show(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday();

        $response = $this->actingAs($admin)->get(route('admin.kitchen.orders.show', $order));

        $response->assertRedirect(route('admin.orders.show', [
            'order' => $order,
            'view' => 'today',
        ]));
    }

    public function test_admin_today_orders_includes_all_statuses_and_payment_states(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();
        $tz = 'Asia/Kolkata';

        $unverified = Order::factory()
            ->for($product)
            ->create([
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'delivery_at' => $this->deliveryAtToday(),
            ]);

        $completed = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'order_status' => 'completed',
                'delivery_at' => $this->deliveryAtToday(),
            ]);

        $cancelled = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'order_status' => 'cancelled',
                'delivery_at' => $this->deliveryAtToday(),
            ]);

        $takeaway = Order::factory()
            ->verified()
            ->for($product)
            ->create([
                'fulfillment_type' => Order::FULFILLMENT_TAKEAWAY,
                'order_status' => 'processing',
                'delivery_at' => $this->deliveryAtToday(),
            ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['view' => 'today']));

        $response->assertOk();
        $response->assertSee($unverified->order_no, false);
        $response->assertSee($completed->order_no, false);
        $response->assertSee($cancelled->order_no, false);
        $response->assertSee($takeaway->order_no, false);
        $response->assertSee(__('Take away'), false);
        $response->assertSee(__("Today's orders"), false);

        $kitchen = $this->kitchenUser();
        $kitchenResponse = $this->actingAs($kitchen)->get(route('admin.kitchen.orders.index'));
        $kitchenResponse->assertDontSee($unverified->order_no, false);
        $kitchenResponse->assertDontSee($completed->order_no, false);
        $kitchenResponse->assertDontSee($cancelled->order_no, false);
        $kitchenResponse->assertSee($takeaway->order_no, false);
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

    public function test_admin_can_mark_delivery_order_delivered_from_completed(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->verified()
            ->deliveryFulfillment()
            ->completed()
            ->create();

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'delivered',
        ]);

        $response->assertRedirect();
        $this->assertSame('delivered', $order->fresh()->order_status);
    }

    public function test_delivered_order_cannot_be_changed(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->verified()
            ->deliveryFulfillment()
            ->create([
                'order_status' => 'delivered',
            ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
        ]);

        $response->assertSessionHasErrors('order_status');
        $this->assertSame('delivered', $order->fresh()->order_status);
    }

    public function test_takeaway_order_cannot_be_marked_delivered(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->verified()
            ->completed()
            ->create([
                'fulfillment_type' => 'takeaway',
            ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'delivered',
        ]);

        $response->assertSessionHasErrors('order_status');
        $this->assertSame('completed', $order->fresh()->order_status);
    }

    public function test_delivery_order_must_be_completed_before_delivered(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->verified()
            ->deliveryFulfillment()
            ->processing()
            ->create();

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'delivered',
        ]);

        $response->assertSessionHasErrors('order_status');
        $this->assertSame('processing', $order->fresh()->order_status);
    }

    private function deliveryAtToday(): Carbon
    {
        $tz = 'Asia/Kolkata';
        $now = Carbon::now($tz);
        $deliveryAt = $now->copy()->addHours(6);

        if (! $deliveryAt->isSameDay($now)) {
            $deliveryAt = $now->copy()->endOfDay()->subHours(2);
        }

        return $deliveryAt->utc();
    }
}
