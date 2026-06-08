<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotificationMail;
use App\Mail\NewOrderNotification;
use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdated;
use App\Mail\PaymentSubmittedNotification;
use App\Mail\PaymentVerifiedNotification;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('admin_email', 'admin@cakeshop.test');
        Setting::flushCache();

        Mail::fake();
    }

    public function test_placing_order_sends_confirmation_to_guest_and_alert_to_admin(): void
    {
        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), $this->validOrderPayload([
            'guest_email' => 'customer@example.com',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('order_placed', true);

        Mail::assertSent(OrderConfirmation::class, function (OrderConfirmation $mail) {
            return $mail->hasTo('customer@example.com');
        });

        Mail::assertSent(NewOrderNotification::class, function (NewOrderNotification $mail) {
            return $mail->hasTo('admin@cakeshop.test');
        });
    }

    public function test_placing_order_without_email_fails_validation(): void
    {
        $product = $this->simpleProduct();
        $payload = $this->validOrderPayload();
        unset($payload['guest_email']);

        $response = $this->post(route('order.store', $product), $payload);

        $response->assertSessionHasErrors('guest_email');
    }

    public function test_submitting_payment_sends_notification_to_admin(): void
    {
        Storage::fake('public');
        $order = Order::factory()->create([
            'guest_email' => 'customer@example.com',
        ]);

        $response = $this->post(route('order.submit-payment.store', $order), [
            'phone' => $order->guest_phone,
            'payment_reference' => 'REF-123',
            'payment_amount' => $order->amount,
            'payment_made_at' => now()->format('Y-m-d\TH:i'),
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertRedirect(route('order.confirm', $order));

        Mail::assertSent(PaymentSubmittedNotification::class, function (PaymentSubmittedNotification $mail) use ($order) {
            return $mail->hasTo('admin@cakeshop.test')
                && $mail->order->is($order)
                && $mail->isUpdate === false;
        });
    }

    public function test_admin_verify_payment_sends_notification_to_guest(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()->paymentDetailsSubmitted()->create([
            'guest_email' => 'customer@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.verify-payment', $order));

        $response->assertRedirect();

        Mail::assertSent(PaymentVerifiedNotification::class, function (PaymentVerifiedNotification $mail) use ($order) {
            return $mail->hasTo('customer@example.com')
                && $mail->order->is($order->fresh());
        });
    }

    public function test_admin_status_update_sends_notification_to_guest(): void
    {
        $admin = $this->adminUser();
        $order = $this->verifiedOrderToday([
            'guest_email' => 'customer@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $this->validPreparationAt($order),
        ]);

        $response->assertRedirect();

        Mail::assertSent(OrderStatusUpdated::class, function (OrderStatusUpdated $mail) use ($order) {
            return $mail->hasTo('customer@example.com')
                && $mail->order->order_status === 'processing'
                && $mail->previousStatus === 'pending';
        });
    }

    public function test_kitchen_status_update_sends_notification_to_guest(): void
    {
        $admin = $this->adminUser();
        $kitchen = $this->kitchenUser();
        $order = $this->verifiedOrderToday([
            'guest_email' => 'customer@example.com',
        ]);

        $this->actingAs($admin)->post(route('admin.orders.update-status', $order), [
            'order_status' => 'processing',
            'preparation_at' => $this->validPreparationAt($order),
        ]);

        Mail::fake();

        $response = $this->actingAs($kitchen)->post(route('admin.kitchen.orders.update-status', $order), [
            'order_status' => 'completed',
        ]);

        $response->assertRedirect();

        Mail::assertSent(OrderStatusUpdated::class, function (OrderStatusUpdated $mail) {
            return $mail->hasTo('customer@example.com')
                && $mail->order->order_status === 'completed'
                && $mail->previousStatus === 'processing';
        });
    }

    public function test_no_admin_mail_when_admin_email_setting_is_empty(): void
    {
        Setting::set('admin_email', '');
        Setting::flushCache();

        $product = $this->simpleProduct();

        $this->post(route('order.store', $product), $this->validOrderPayload([
            'guest_email' => 'customer@example.com',
        ]));

        Mail::assertSent(OrderConfirmation::class, 1);
        Mail::assertNotSent(NewOrderNotification::class);
    }

    public function test_order_notifications_are_dispatched_to_queue(): void
    {
        Queue::fake();

        $product = $this->simpleProduct();

        $this->post(route('order.store', $product), $this->validOrderPayload([
            'guest_email' => 'customer@example.com',
        ]));

        Queue::assertPushed(SendOrderNotificationMail::class, 2);
        Queue::assertPushed(SendOrderNotificationMail::class, function (SendOrderNotificationMail $job) {
            return $job->recipient === 'customer@example.com'
                && $job->mailable instanceof OrderConfirmation;
        });
        Queue::assertPushed(SendOrderNotificationMail::class, function (SendOrderNotificationMail $job) {
            return $job->recipient === 'admin@cakeshop.test'
                && $job->mailable instanceof NewOrderNotification;
        });
    }

    public function test_mail_failure_does_not_block_order_placement(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), $this->validOrderPayload([
            'guest_email' => 'customer@example.com',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('order_placed', true);
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'guest_email' => 'customer@example.com',
        ]);
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
