<?php

namespace Tests\Feature;

use App\Events\StaffNotificationBroadcasted;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\KitchenOrderQueuedTodayNotification;
use App\Notifications\KitchenPaymentVerifiedTodayNotification;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderCompletedAdminNotification;
use App\Services\InAppOrderNotificationService;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InAppOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('notifications_enabled', '1');
        Setting::flushCache();

        Mail::fake();
        Queue::fake();
    }

    public function test_new_order_notifies_all_admin_users_in_database(): void
    {
        $adminOne = $this->adminUser();
        $adminTwo = User::factory()->create(['email_verified_at' => now()]);
        $adminTwo->assignRole('Admin');
        $kitchen = $this->kitchenUser();

        $product = $this->simpleProduct();
        $this->post(route('order.store', $product), $this->validOrderPayload());

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $adminOne->id,
            'type' => NewOrderAdminNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $adminTwo->id,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $kitchen->id,
        ]);
    }

    public function test_kitchen_only_receives_notifications_for_visible_orders(): void
    {
        $kitchen = $this->kitchenUser();
        $order = Order::factory()->for(Product::factory()->create())->create([
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        app(InAppOrderNotificationService::class)->notifyKitchen(
            new KitchenOrderQueuedTodayNotification($order)
        );

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $kitchen->id,
        ]);
    }

    public function test_payment_verified_today_notifies_kitchen_users(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday(['order_status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.orders.verify-payment', $order));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $kitchen->id,
            'type' => KitchenPaymentVerifiedTodayNotification::class,
        ]);
    }

    public function test_kitchen_queue_entry_notifies_kitchen_users(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday(['order_status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'processing',
                'preparation_at' => $this->validPreparationAt($order),
            ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $kitchen->id,
            'type' => KitchenOrderQueuedTodayNotification::class,
        ]);
    }

    public function test_kitchen_complete_notifies_admin_users(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday([
            'order_status' => 'processing',
            'preparation_at' => now()->addHour(),
        ]);

        $this->actingAs($kitchen)
            ->post(route('admin.kitchen.orders.update-status', $order), [
                'order_status' => 'completed',
            ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => OrderCompletedAdminNotification::class,
        ]);
    }

    public function test_dedupe_prevents_duplicate_notifications(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()->for(Product::factory()->create())->create();

        $service = app(InAppOrderNotificationService::class);
        $notification = new NewOrderAdminNotification($order);

        $service->notifyAdmins($notification);
        $service->notifyAdmins($notification);

        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_live_broadcast_dispatched_after_database_write(): void
    {
        Event::fake([StaffNotificationBroadcasted::class]);
        Setting::setEncrypted('pusher_app_id', 'test-app-id');
        Setting::setEncrypted('pusher_app_key', 'test-app-key');
        Setting::setEncrypted('pusher_app_secret', 'test-app-secret');
        Setting::setEncrypted('pusher_app_cluster', 'ap2');
        Setting::flushCache();

        $this->adminUser();
        $order = Order::factory()->for(Product::factory()->create())->create();

        app(InAppOrderNotificationService::class)->notifyAdmins(new NewOrderAdminNotification($order));

        Event::assertDispatched(StaffNotificationBroadcasted::class);
    }

    public function test_admin_layout_renders_notification_badge_from_database(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()->for(Product::factory()->create())->create();
        $admin->notify(new NewOrderAdminNotification($order));

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-notification-badge', false);
        $response->assertSee($order->order_no);
        $response->assertSee('payment_review', false);
    }

    public function test_mark_read_api_sets_read_at(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()->for(Product::factory()->create())->create();
        $admin->notify(new NewOrderAdminNotification($order));
        $notification = $admin->unreadNotifications()->first();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.notifications.read', $notification->id));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($notification->fresh()->read_at);
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

    private function simpleProduct(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validOrderPayload(array $overrides = []): array
    {
        $rules = app(OrderService::class)->deliveryAtRules();
        $delivery = Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');

        return array_merge([
            'guest_name' => 'Test Customer',
            'guest_email' => 'customer@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $delivery,
            'fulfillment_type' => 'takeaway',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function verifiedOrderToday(array $overrides = []): Order
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
            ->create(array_merge([
                'delivery_at' => $deliveryAt->utc(),
            ], $overrides));
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
