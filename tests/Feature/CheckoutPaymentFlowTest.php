<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\AuthGuards;
use App\Services\CustomerAuthService;
use App\Services\OrderService;
use App\Services\Payments\Gateways\RazorpayGateway;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Hexadog\ThemesManager\Facades\ThemesManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Payments\FakeRazorpayGateway;
use Tests\TestCase;

class CheckoutPaymentFlowTest extends TestCase
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

    private function createProduct(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
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
            'guest_name' => 'Pay First Buyer',
            'guest_email' => 'payfirst@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => Order::FULFILLMENT_TAKEAWAY,
        ], $overrides);
    }

    private function captureOtpForEmail(string $email): string
    {
        Mail::fake();
        app(CustomerAuthService::class)->sendOtp($email);

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return (string) $code;
    }

    private function verifyCheckoutOtpViaEndpoint(string $email, string $code, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('order.checkout.verify-otp'), array_merge([
            'email' => $email,
            'code' => $code,
            'guest_name' => 'Pay First Buyer',
            'guest_phone' => '9876543210',
        ], $overrides));
    }

    public function test_prepare_finalize_creates_verified_order(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->customer()->create();

        $prepare = $this->actingAs($customer)->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );

        $prepare->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('gateway_order_id', 'order_fake_123');

        $checkoutReference = $prepare->json('checkout_reference');

        $finalize = $this->actingAs($customer)->postJson(route('order.checkout.finalize'), [
            'checkout_reference' => $checkoutReference,
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_checkout_789',
            'razorpay_signature' => 'sig_fake',
        ]);

        $finalize->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['redirect_url']);

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame('verified', $order->payment_status);
        $this->assertSame(Order::PAYMENT_METHOD_RAZORPAY, $order->payment_method);
        $this->assertSame(500.0, (float) $order->amount);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway_payment_id' => 'pay_checkout_789',
            'status' => 'paid',
        ]);

        $order->refresh();
        $this->assertSame('pay_checkout_789', $order->payment_reference);
        $this->assertSame(500.0, (float) $order->payment_amount);
        $this->assertNotNull($order->payment_made_at);
    }

    public function test_store_is_blocked_when_pay_before_order_enabled(): void
    {
        $product = $this->createProduct();
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->post(
            route('order.store', $product),
            $this->orderPayload()
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors('checkout');
        $this->assertSame(0, Order::query()->count());
    }

    public function test_confirm_page_shows_verified_state_for_paid_checkout_order(): void
    {
        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'verified',
            'amount' => 500,
        ]);

        $this->get(route('order.confirm', $order))
            ->assertOk()
            ->assertSee(__('Payment verified'), false)
            ->assertDontSee(__('payments.pay_now'), false);
    }

    public function test_guest_prepare_and_finalize_creates_verified_order(): void
    {
        Mail::fake();
        $product = $this->createProduct();

        app(CustomerAuthService::class)->sendOtp('payfirst@example.com');

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        app(CustomerAuthService::class)->verifyOtp('payfirst@example.com', $code);

        $prepare = $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );

        $prepare->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('gateway_order_id', 'order_fake_123');

        $checkoutReference = $prepare->json('checkout_reference');

        $finalize = $this->postJson(route('order.checkout.finalize'), [
            'checkout_reference' => $checkoutReference,
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_guest_checkout_456',
            'razorpay_signature' => 'sig_fake',
        ]);

        $finalize->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['redirect_url']);

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame('verified', $order->payment_status);
        $this->assertSame('payfirst@example.com', $order->guest_email);
        $this->assertAuthenticated('customer');
    }

    public function test_guest_prepare_requires_verified_otp(): void
    {
        Mail::fake();
        $product = $this->createProduct();

        $prepare = $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );

        $prepare->assertStatus(422);

        app(CustomerAuthService::class)->sendOtp('payfirst@example.com');

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        app(CustomerAuthService::class)->verifyOtp('payfirst@example.com', $code);

        $prepareAfterOtp = $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );

        $prepareAfterOtp->assertOk()->assertJsonPath('success', true);
    }

    public function test_checkout_verify_otp_authenticates_guest(): void
    {
        $email = 'payfirst@example.com';
        $code = $this->captureOtpForEmail($email);

        $response = $this->verifyCheckoutOtpViaEndpoint($email, $code);

        $response->assertOk()
            ->assertJsonPath('verified', true)
            ->assertJsonPath('authenticated', true)
            ->assertJsonStructure(['csrf_token']);

        $this->assertAuthenticated(AuthGuards::CUSTOMER);
        $this->assertNull(session(CustomerAuthService::SESSION_VERIFIED_EMAIL));
        $this->assertNotNull(User::where('email', $email)->first());
    }

    public function test_authenticated_guest_can_prepare_without_reverifying_otp(): void
    {
        $product = $this->createProduct();
        $email = 'payfirst@example.com';
        $code = $this->captureOtpForEmail($email);

        $this->verifyCheckoutOtpViaEndpoint($email, $code)->assertOk();

        $firstPrepare = $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );
        $firstPrepare->assertOk()->assertJsonPath('success', true);

        $secondPrepare = $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        );
        $secondPrepare->assertOk()->assertJsonPath('success', true);
    }

    public function test_consumed_otp_not_required_after_login(): void
    {
        $product = $this->createProduct();
        $email = 'payfirst@example.com';
        $code = $this->captureOtpForEmail($email);

        $this->verifyCheckoutOtpViaEndpoint($email, $code)->assertOk();

        $this->verifyCheckoutOtpViaEndpoint($email, $code)->assertForbidden();

        $this->postJson(
            route('order.checkout.prepare', $product),
            $this->orderPayload()
        )->assertOk()->assertJsonPath('success', true);
    }
}
