<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Payments\Gateways\RazorpayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Payments\FakeRazorpayGateway;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('theme', 'better-buns');
        Setting::set('currency', 'INR');
        Setting::setEncrypted('razorpay_key_id', 'rzp_test_key');
        Setting::setEncrypted('razorpay_key_secret', 'rzp_test_secret');
        Setting::set('payment_gateway', 'razorpay');
        Setting::flushCache();
    }

    public function test_invalid_signature_returns_friendly_error(): void
    {
        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway(validSignature: false));

        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'pending',
            'amount' => 500,
        ]);

        $this->postJson(route('order.payment.initiate', $order))->assertOk();

        $response = $this->postJson(route('order.payment.verify', $order), [
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_fake_456',
            'razorpay_signature' => 'bad_sig',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('payments.errors.signature_invalid'))
            ->assertJsonPath('retryable', true);

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_amount_mismatch_rejects_payment(): void
    {
        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway(verifyAmount: 100.0));

        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'pending',
            'amount' => 500,
        ]);

        $this->postJson(route('order.payment.initiate', $order))->assertOk();

        $this->postJson(route('order.payment.verify', $order), [
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_fake_789',
            'razorpay_signature' => 'sig_fake',
        ])->assertStatus(422)
            ->assertJsonPath('message', __('payments.errors.amount_mismatch'));

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_duplicate_verify_is_idempotent(): void
    {
        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway);

        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'pending',
            'amount' => 500,
        ]);

        $this->postJson(route('order.payment.initiate', $order))->assertOk();

        $payload = [
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_fake_dup',
            'razorpay_signature' => 'sig_fake',
        ];

        $this->postJson(route('order.payment.verify', $order), $payload)->assertOk();
        $this->postJson(route('order.payment.verify', $order), $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, Payment::query()->where('gateway_payment_id', 'pay_fake_dup')->count());
    }

    public function test_warm_theme_cannot_initiate_online_checkout(): void
    {
        Setting::set('theme', 'warm');
        Setting::flushCache();

        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway);

        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_UPI,
            'payment_status' => 'pending',
        ]);

        $this->postJson(route('order.payment.initiate', $order))
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
