<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payments\Gateways\RazorpayGateway;
use Hexadog\ThemesManager\Facades\ThemesManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Payments\FakeRazorpayGateway;
use Tests\TestCase;

class RazorpayPaymentFlowTest extends TestCase
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

        ThemesManager::set('cakeshop/better-buns');

        $this->app->bind(RazorpayGateway::class, fn () => new FakeRazorpayGateway);
    }

    public function test_initiate_and_verify_marks_order_paid(): void
    {
        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'pending',
            'amount' => 500,
        ]);

        $initiate = $this->postJson(route('order.payment.initiate', $order));
        $initiate->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('gateway_order_id', 'order_fake_123');

        $verify = $this->postJson(route('order.payment.verify', $order), [
            'razorpay_order_id' => 'order_fake_123',
            'razorpay_payment_id' => 'pay_fake_456',
            'razorpay_signature' => 'sig_fake',
        ]);

        $verify->assertOk()->assertJsonPath('success', true);

        $order->refresh();
        $this->assertSame('verified', $order->payment_status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway_payment_id' => 'pay_fake_456',
            'status' => 'paid',
        ]);
    }

    public function test_better_buns_confirm_shows_pay_now_for_legacy_pending_orders(): void
    {
        $order = Order::factory()->create([
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'payment_status' => 'pending',
        ]);

        $this->get(route('order.confirm', $order))
            ->assertOk()
            ->assertSee(__('payments.pay_now'), false)
            ->assertSee('data-razorpay-checkout', false);
    }
}
