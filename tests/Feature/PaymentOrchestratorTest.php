<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payments\PaymentOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('theme', 'better-buns');
        Setting::flushCache();
    }

    public function test_better_buns_order_uses_razorpay_pending(): void
    {
        $order = new Order;
        app(PaymentOrchestrator::class)->initializeOrderPayment($order);

        $this->assertSame(Order::PAYMENT_METHOD_RAZORPAY, $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_warm_theme_order_uses_upi_pending(): void
    {
        Setting::set('theme', 'warm');
        Setting::flushCache();

        $order = new Order;
        app(PaymentOrchestrator::class)->initializeOrderPayment($order);

        $this->assertSame(Order::PAYMENT_METHOD_UPI, $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
    }
}
