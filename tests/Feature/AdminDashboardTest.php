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
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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

    public function test_admin_dashboard_shows_operational_sections(): void
    {
        $admin = $this->adminUser();
        $todayOrder = $this->verifiedOrderToday();
        $upcomingOrder = $this->verifiedOrderTomorrow();

        $this->actingAs($admin)->post(route('admin.orders.update-status', $todayOrder), [
            'order_status' => 'processing',
            'preparation_at' => $this->validPreparationAt($todayOrder),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText(__("Today's Deliveries"));
        $response->assertSeeText(__('In Kitchen'));
        $response->assertSeeText(__('Upcoming'));
        $response->assertSeeText(__('Deliveries today'));
        $response->assertSeeText(__('Revenue today'));
        $response->assertSee($todayOrder->order_no, false);
        $response->assertSee($upcomingOrder->order_no, false);
    }

    public function test_today_deliveries_includes_pending_unverified_orders(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->for(Product::factory())
            ->create([
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'delivery_at' => $this->deliveryAtToday(),
            ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee($order->order_no, false);
        $response->assertSee(__('Pending'), false);
    }

    public function test_in_kitchen_section_only_shows_processing_queue(): void
    {
        $admin = $this->adminUser();
        $inKitchen = $this->verifiedOrderToday();
        $pendingToday = Order::factory()
            ->verified()
            ->for(Product::factory())
            ->deliveryToday()
            ->create(['order_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $inKitchen), [
            'order_status' => 'processing',
            'preparation_at' => $this->validPreparationAt($inKitchen),
        ]);

        $kitchenOrders = app(OrderService::class)->listKitchenTodayActionableForDashboard();
        $this->assertTrue($kitchenOrders->contains('id', $inKitchen->id));
        $this->assertFalse($kitchenOrders->contains('id', $pendingToday->id));

        $visibleToday = app(OrderService::class)->listKitchenTodayForDashboard();
        $this->assertTrue($visibleToday->contains('id', $pendingToday->id));

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee($inKitchen->displayProductName(), false);
    }

    public function test_upcoming_preview_includes_unverified_future_orders(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->for(Product::factory())
            ->create([
                'payment_status' => 'pending',
                'delivery_at' => Carbon::now('Asia/Kolkata')->addDays(2)->utc(),
            ]);

        $preview = app(OrderService::class)->listAdminUpcomingPreview();
        $this->assertTrue($preview->contains('id', $order->id));

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee($order->order_no, false);
    }

    public function test_upcoming_preview_respects_limit_of_six(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();
        $tz = 'Asia/Kolkata';

        for ($i = 1; $i <= 8; $i++) {
            Order::factory()
                ->for($product)
                ->create([
                    'delivery_at' => Carbon::now($tz)->addDays($i)->utc(),
                ]);
        }

        $preview = app(OrderService::class)->listAdminUpcomingPreview();
        $this->assertCount(OrderService::ADMIN_UPCOMING_PREVIEW_LIMIT, $preview);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee(__('+:count more', ['count' => 2]), false);
    }

    public function test_payment_review_shows_only_submitted_unverified_orders(): void
    {
        $admin = $this->adminUser();
        $awaiting = Order::factory()
            ->for(Product::factory())
            ->paymentDetailsSubmitted()
            ->create(['payment_status' => 'pending']);

        $barePending = Order::factory()
            ->for(Product::factory())
            ->create(['payment_status' => 'pending']);

        $preview = app(OrderService::class)->listAdminPaymentReviewPreview();
        $this->assertTrue($preview->contains('id', $awaiting->id));
        $this->assertFalse($preview->contains('id', $barePending->id));

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee($awaiting->order_no, false);
    }

    public function test_payment_review_preview_respects_limit_of_five(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        for ($i = 0; $i < 7; $i++) {
            Order::factory()
                ->for($product)
                ->paymentDetailsSubmitted()
                ->create([
                    'payment_status' => 'pending',
                    'payment_made_at' => now()->subHours($i + 1),
                ]);
        }

        $preview = app(OrderService::class)->listAdminPaymentReviewPreview();
        $this->assertCount(OrderService::ADMIN_PAYMENT_REVIEW_PREVIEW_LIMIT, $preview);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee(__('+:count more to review', ['count' => 2]), false);
    }

    public function test_list_for_admin_filters_delivery_today(): void
    {
        $admin = $this->adminUser();
        $today = $this->verifiedOrderToday();
        $tomorrow = $this->verifiedOrderTomorrow();

        $request = Request::create('/admin/orders', 'GET', ['view' => 'today']);
        $paginator = app(OrderService::class)->listForAdmin($request);

        $ids = $paginator->getCollection()->pluck('id');
        $this->assertTrue($ids->contains($today->id));
        $this->assertFalse($ids->contains($tomorrow->id));
    }

    public function test_list_for_admin_filters_awaiting_payment_verification(): void
    {
        $admin = $this->adminUser();
        $awaiting = Order::factory()
            ->for(Product::factory())
            ->paymentDetailsSubmitted()
            ->create(['payment_status' => 'pending']);
        $barePending = Order::factory()
            ->for(Product::factory())
            ->create(['payment_status' => 'pending']);

        $request = Request::create('/admin/orders', 'GET', ['awaiting_payment_verification' => '1']);
        $paginator = app(OrderService::class)->listForAdmin($request);

        $ids = $paginator->getCollection()->pluck('id');
        $this->assertTrue($ids->contains($awaiting->id));
        $this->assertFalse($ids->contains($barePending->id));
    }

    public function test_admin_without_orders_view_sees_legacy_dashboard(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $role = \Spatie\Permission\Models\Role::findByName('Admin');
        $role->revokePermissionTo('orders.view');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText(__('Overview of your store'));
        $response->assertDontSeeText(__("Today's deliveries"));
    }

    public function test_admin_layout_includes_collapsible_sidebar_markup(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-admin-sidebar-toggle', false);
        $response->assertSee('id="admin-sidebar"', false);
        $response->assertSee('data-admin-sidebar-backdrop', false);
        $response->assertSee('aria-controls="admin-sidebar"', false);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
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

    private function deliveryAtToday(): \Carbon\Carbon
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
