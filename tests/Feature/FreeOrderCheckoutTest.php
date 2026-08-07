<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Services\Payments\Gateways\RazorpayGateway;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Hexadog\ThemesManager\Facades\ThemesManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Payments\FakeRazorpayGateway;
use Tests\TestCase;

class FreeOrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('theme', 'better-buns');
        Setting::set('currency', 'INR');
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::setEncrypted('razorpay_key_id', 'rzp_test_key');
        Setting::setEncrypted('razorpay_key_secret', 'rzp_test_secret');
        Setting::set('payment_gateway', 'razorpay');
        Setting::flushCache();

        ThemesManager::set('cakeshop/better-buns');

        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway);
    }

    private function createProduct(float $price = 500): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'price' => $price,
            'status' => 'active',
        ]);
    }

    private function validDeliveryAt(): string
    {
        $rules = app(OrderService::class)->deliveryAtRules();

        return Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');
    }

    private function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'guest_name' => 'Free Order Buyer',
            'guest_email' => 'freeorder@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => Order::FULFILLMENT_TAKEAWAY,
        ], $overrides);
    }

    public function test_100_percent_discount_skips_razorpay_and_creates_verified_free_order(): void
    {
        $product = $this->createProduct(500);
        $customer = User::factory()->customer()->create();

        Coupon::factory()->fixed(500)->create(['code' => 'FREE100']);

        $prepare = $this->actingAs($customer)->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload(['coupon_code' => 'FREE100'])
        );

        $prepare->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('free_order', true)
            ->assertJsonPath('amount', 0);

        $checkoutReference = $prepare->json('checkout_reference');

        $finalize = $this->actingAs($customer)->postJson(route('order.checkout.finalize-free'), [
            'checkout_reference' => $checkoutReference,
        ]);

        $finalize->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['redirect_url']);

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame('verified', $order->payment_status);
        $this->assertSame(0.0, (float) $order->amount);
        $this->assertSame(500.0, (float) $order->discount_amount);
        $this->assertSame('FREE100', $order->coupon_code);
    }

    public function test_free_order_finalize_rejects_tampered_nonzero_session(): void
    {
        $product = $this->createProduct(500);
        $customer = User::factory()->customer()->create();

        // Prepare a normal (non-free) checkout, then attempt to finalize it via the
        // free-order endpoint — the cached session's gateway isn't "free" so this
        // must be rejected rather than silently creating an unpaid order.
        $prepare = $this->actingAs($customer)->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );

        $prepare->assertOk()->assertJsonPath('success', true);
        $this->assertArrayNotHasKey('free_order', $prepare->json());

        $checkoutReference = $prepare->json('checkout_reference');

        $finalize = $this->actingAs($customer)->postJson(route('order.checkout.finalize-free'), [
            'checkout_reference' => $checkoutReference,
        ]);

        $finalize->assertStatus(422);
        $this->assertSame(0, Order::query()->count());
    }
}
