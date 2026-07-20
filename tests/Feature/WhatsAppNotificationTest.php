<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderNotificationService;
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

    public function test_customer_and_admin_whatsapp_dispatched_on_order_placed(): void
    {
        $this->enableWhatsApp();
        Bus::fake();

        $order = Order::factory()->create([
            'guest_name' => 'Buyer',
            'guest_phone' => '9558517748',
        ]);

        app(OrderNotificationService::class)->notifyOrderPlaced($order);

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) use ($order) {
            return $job->phone === '9558517748'
                && $job->lang === 'en_US'
                && $job->dynamicUrlButtonSuffix === $order->customerOrderWhatsAppUrlSuffix();
        });

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) {
            return $job->phone === '919999999999';
        });
    }

    public function test_customer_whatsapp_not_dispatched_when_flag_disabled(): void
    {
        $this->enableWhatsApp([
            'services.whatsapp.customer_order_notifications' => false,
            'services.whatsapp.admin_number' => '',
        ]);
        Bus::fake();

        $order = Order::factory()->create(['guest_phone' => '9558517748']);

        app(OrderNotificationService::class)->notifyStatusUpdated($order, 'pending');

        Bus::assertNotDispatched(SendWhatsAppNotification::class);
    }

    public function test_status_update_dispatches_customer_whatsapp(): void
    {
        $this->enableWhatsApp(['services.whatsapp.admin_number' => '']);
        Bus::fake();

        $order = Order::factory()->create([
            'guest_phone' => '9558517748',
            'order_status' => 'processing',
        ]);

        app(OrderNotificationService::class)->notifyStatusUpdated($order, 'pending');

        Bus::assertDispatched(SendWhatsAppNotification::class, function (SendWhatsAppNotification $job) {
            return $job->phone === '9558517748';
        });
    }
}
