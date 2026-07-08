<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceablePincode;
use App\Models\Setting;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\OrderService;
use App\Support\AuthGuards;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartialInStoreCashPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('kitchen_lead_hours', '');
        Setting::flushCache();

        ServiceablePincode::factory()->create([
            'pincode' => '360004',
            'locality' => 'Kalawad Road',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->flushSession();

        parent::tearDown();
    }

    private function validDeliveryAt(): string
    {
        $rules = app(OrderService::class)->deliveryAtRules();

        return Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');
    }

    private function impersonateAndPlace(User $admin, User $customer, Product $product, array $extra = []): Order
    {
        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.customers.impersonate', $customer))
            ->assertRedirect(route('products.index'));

        $payload = array_merge([
            'guest_name' => $customer->name,
            'guest_phone' => $customer->phone,
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => 'takeaway',
        ], $extra);

        $response = $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('order.store', $product), $payload);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_in_store_order_with_zero_cash_is_verified_on_placement_and_kitchen_visible(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'Asia/Kolkata'));

        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 800]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 0,
            'delivery_at' => Carbon::now('Asia/Kolkata')->addHours(8)->format('Y-m-d\TH:i'),
        ]);

        $this->assertSame(Order::PAYMENT_STATUS_VERIFIED, $order->payment_status);
        $this->assertSame(0.0, (float) $order->payment_amount);
        $this->assertSame(800.0, $order->balanceDue());
        $this->assertTrue($order->isVerifiedWithOutstandingBalance());

        $this->assertTrue(
            Order::query()->whereKey($order->id)->kitchenTodayVisible()->exists()
        );

        $prepAt = Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i');

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'processing',
                'preparation_at' => $prepAt,
            ])
            ->assertRedirect();

        $this->actingAs($admin, AuthGuards::STAFF)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee(__('Payment verified — balance still due'), false);
    }

    public function test_in_store_order_with_partial_cash_is_verified_on_placement(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 1000]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 300,
        ]);

        $this->assertSame(Order::PAYMENT_STATUS_VERIFIED, $order->payment_status);
        $this->assertSame(300.0, (float) $order->payment_amount);
        $this->assertSame(700.0, $order->balanceDue());
        $this->assertTrue($order->isVerifiedWithOutstandingBalance());
    }

    public function test_in_store_order_with_full_cash_is_verified(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 500,
        ]);

        $this->assertSame(Order::PAYMENT_STATUS_VERIFIED, $order->payment_status);
        $this->assertSame(500.0, (float) $order->payment_amount);
        $this->assertSame(0.0, $order->balanceDue());
    }

    public function test_admin_can_record_remaining_cash_and_mark_order_verified(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 900]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 200,
        ]);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.record-cash-payment', $order), [
                'amount_received' => 700,
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::PAYMENT_STATUS_VERIFIED, $order->payment_status);
        $this->assertSame(900.0, (float) $order->payment_amount);
        $this->assertSame(0.0, $order->balanceDue());
    }

    public function test_admin_can_mark_delivered_with_outstanding_in_store_balance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'Asia/Kolkata'));

        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 600]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 100,
            'fulfillment_type' => Order::FULFILLMENT_DELIVERY,
            'delivery_address' => '12 Main Street',
            'delivery_pincode' => '360004',
            'delivery_at' => Carbon::now('Asia/Kolkata')->addHours(8)->format('Y-m-d\TH:i'),
        ]);

        $prepAt = Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i');

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'processing',
                'preparation_at' => $prepAt,
            ])
            ->assertRedirect();

        $order->refresh();

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'completed',
            ])
            ->assertRedirect();

        $order->refresh();

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'delivered',
            ])
            ->assertRedirect();

        $this->assertSame('delivered', $order->fresh()->order_status);
        $this->assertTrue($order->fresh()->hasOutstandingBalance());
    }

    public function test_cash_received_above_order_total_is_rejected(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 400]);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.customers.impersonate', $customer));

        $response = $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('order.store', $product), [
                'guest_name' => $customer->name,
                'guest_phone' => $customer->phone,
                'quantity' => 1,
                'delivery_at' => $this->validDeliveryAt(),
                'fulfillment_type' => 'takeaway',
                'cash_received' => 500,
            ]);

        $response->assertSessionHasErrors('cash_received');
        $this->assertSame(0, Order::count());
    }

    public function test_online_pending_order_still_blocks_status_change(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->for(Product::factory())->create([
            'payment_method' => Order::PAYMENT_METHOD_UPI,
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'placed_by_user_id' => null,
        ]);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'processing',
                'preparation_at' => Carbon::now('Asia/Kolkata')->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors('_form');

        $this->assertSame('pending', $order->fresh()->order_status);
    }

    public function test_admin_can_verify_legacy_in_store_partial_order_without_changing_cash_received(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->for(Product::factory()->create(['price' => 750]))->create([
            'payment_method' => Order::PAYMENT_METHOD_CASH_ON_STORE,
            'payment_status' => Order::PAYMENT_STATUS_PARTIALLY_PAID,
            'payment_amount' => 250,
            'placed_by_user_id' => $admin->id,
            'amount' => 750,
        ]);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.verify-payment', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_STATUS_VERIFIED, $order->payment_status);
        $this->assertSame(250.0, (float) $order->payment_amount);
        $this->assertSame(500.0, $order->balanceDue());
        $this->assertTrue($order->isVerifiedWithOutstandingBalance());
    }

    public function test_verified_in_store_order_with_balance_allows_kitchen_status_change(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'Asia/Kolkata'));

        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 800]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 100,
            'delivery_at' => Carbon::now('Asia/Kolkata')->addHours(8)->format('Y-m-d\TH:i'),
        ]);

        $this->assertTrue($order->isVerifiedWithOutstandingBalance());

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order), [
                'order_status' => 'processing',
                'preparation_at' => Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('processing', $order->order_status);
        $this->assertTrue($order->isVerifiedWithOutstandingBalance());
    }

    public function test_kitchen_status_form_does_not_warn_about_outstanding_balance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'Asia/Kolkata'));

        $admin = $this->admin();
        $kitchen = User::factory()->create();
        $kitchen->assignRole('Kitchen');
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 800]);

        $order = $this->impersonateAndPlace($admin, $customer, $product, [
            'cash_received' => 100,
            'delivery_at' => Carbon::now('Asia/Kolkata')->addHours(8)->format('Y-m-d\TH:i'),
        ]);

        $this->assertTrue($order->isVerifiedWithOutstandingBalance());

        $prepAt = Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i');

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.orders.update-status', $order->fresh()), [
                'order_status' => 'processing',
                'preparation_at' => $prepAt,
            ])
            ->assertRedirect();

        $order->refresh();

        $this->actingAs($kitchen)
            ->get(route('admin.kitchen.orders.show', $order))
            ->assertOk()
            ->assertSee('data-order-status-form', false)
            ->assertSee('data-has-outstanding-balance="0"', false)
            ->assertDontSee('data-has-outstanding-balance="1"', false);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('data-has-outstanding-balance="1"', false);
    }

    public function test_place_form_shows_in_store_cash_fields_when_impersonating(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->customer()->create([
            'phone' => '9876000100',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);
        $product = Product::factory()->create(['status' => 'active', 'slug' => 'partial-cash-cake']);

        $this->actingAs($admin, AuthGuards::STAFF)
            ->post(route('admin.customers.impersonate', $customer));

        $this->actingAs($admin, AuthGuards::STAFF)
            ->get(route('order.place', $product))
            ->assertOk()
            ->assertSee(__('Cash received now'), false)
            ->assertSee('data-is-impersonating="1"', false);
    }
}
