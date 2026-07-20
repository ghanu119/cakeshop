<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderNotificationService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::flushCache();
    }

    private function enableWhatsApp(array $overrides = []): void
    {
        config(array_merge([
            'services.whatsapp.enabled' => true,
            'services.whatsapp.phone_number_id' => 'PNID',
            'services.whatsapp.access_token' => 'token',
            'services.whatsapp.order_template' => 'order_update',
            'services.whatsapp.order_template_lang' => 'en_US',
            'services.whatsapp.customer_order_notifications' => true,
            'services.whatsapp.admin_number' => '919999999999',
        ], $overrides));
    }

    public function test_online_order_placed_dispatches_admin_whatsapp_only_not_customer(): void
    {
        $this->enableWhatsApp();
        Bus::fake();

        $order = Order::factory()->create([
            'guest_name' => 'Buyer',
            'guest_phone' => '9558517748',
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
        ]);

        app(OrderNotificationService::class)->notifyOrderPlaced($order);

        Bus::assertNotDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) {
            return $job->phone === '9558517748';
        });

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) use ($order) {
            return $job->phone === '919999999999'
                && $job->lang === 'en_US'
                && $job->dynamicUrlButtonSuffix === (string) $order->uuid
                && ($job->bodyParams[2] ?? '') === $order->whatsappDeliveryTimeLine();
        });
    }

    public function test_in_store_order_placed_dispatches_customer_and_admin_whatsapp(): void
    {
        $this->enableWhatsApp();
        Bus::fake();

        $order = Order::factory()->inStorePending()->create([
            'guest_name' => 'Buyer',
            'guest_phone' => '9558517748',
            'payment_amount' => 0,
        ]);

        app(OrderNotificationService::class)->notifyOrderPlaced($order);

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) use ($order) {
            return $job->phone === '9558517748'
                && $job->bodyParams[2] === $order->whatsappDeliveryTimeLine();
        });

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) {
            return $job->phone === '919999999999';
        });
    }

    public function test_payment_verified_online_dispatches_customer_order_confirm_whatsapp(): void
    {
        $this->enableWhatsApp(['services.whatsapp.admin_number' => '']);
        Bus::fake();

        $deliveryAt = Carbon::parse('2026-07-25 14:30:00', 'Asia/Kolkata')->utc();

        $order = Order::factory()->verified()->create([
            'guest_phone' => '9558517748',
            'payment_method' => Order::PAYMENT_METHOD_RAZORPAY,
            'placed_by_user_id' => null,
            'delivery_at' => $deliveryAt,
        ]);

        app(OrderNotificationService::class)->notifyPaymentVerified($order);

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) use ($order) {
            return $job->phone === '9558517748'
                && $job->bodyParams[2] === '25 Jul 2026, 14:30'
                && $job->bodyParams[2] !== __('Payment verified')
                && $job->bodyParams[2] !== __('Pending');
        });
    }

    public function test_payment_verified_in_store_does_not_dispatch_second_customer_whatsapp(): void
    {
        $this->enableWhatsApp(['services.whatsapp.admin_number' => '']);
        Bus::fake();

        $order = Order::factory()->inStoreVerified()->create([
            'guest_phone' => '9558517748',
        ]);

        app(OrderNotificationService::class)->notifyPaymentVerified($order);

        Bus::assertNotDispatched(SendWhatsAppNotification::class);
    }

    public function test_customer_whatsapp_not_dispatched_when_flag_disabled(): void
    {
        $this->enableWhatsApp([
            'services.whatsapp.customer_order_notifications' => false,
            'services.whatsapp.admin_number' => '',
        ]);
        Bus::fake();

        $order = Order::factory()->inStorePending()->create(['guest_phone' => '9558517748']);

        app(OrderNotificationService::class)->notifyOrderPlaced($order);

        Bus::assertNotDispatched(SendWhatsAppNotification::class);
    }

    public function test_status_update_does_not_dispatch_customer_whatsapp(): void
    {
        $this->enableWhatsApp(['services.whatsapp.admin_number' => '']);
        Bus::fake();

        $order = Order::factory()->create([
            'guest_phone' => '9558517748',
            'order_status' => 'processing',
        ]);

        app(OrderNotificationService::class)->notifyStatusUpdated($order, 'pending');

        Bus::assertNotDispatched(SendWhatsAppNotification::class);
    }

    public function test_whatsapp_delivery_time_line_fallback_when_delivery_at_missing(): void
    {
        $order = Order::factory()->make(['delivery_at' => null]);

        $this->assertSame(__('To be confirmed'), $order->whatsappDeliveryTimeLine());
    }
}
