<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteSetting;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::flushCache();
    }

    public function test_confirm_page_shows_submit_cta_when_no_payment_details(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee($order->order_no, false);
        $response->assertSee(__('Payment required'), false);
        $response->assertSee(__('Submit payment details'), false);
        $response->assertSee(__('Copy order number'), false);
        $response->assertDontSee(__('Payment details received'), false);
    }

    public function test_order_routes_use_uuid_not_order_no(): void
    {
        $order = Order::factory()->create();

        $confirmUrl = route('order.confirm', $order);
        $paymentUrl = route('order.submit-payment', $order);
        $adminUrl = route('admin.orders.show', $order);

        $this->assertStringContainsString($order->uuid, $confirmUrl);
        $this->assertStringContainsString($order->uuid, $paymentUrl);
        $this->assertStringContainsString($order->uuid, $adminUrl);
        $this->assertStringNotContainsString($order->order_no, $confirmUrl);
        $this->assertStringNotContainsString($order->order_no, $paymentUrl);
        $this->assertStringNotContainsString($order->order_no, $adminUrl);
    }

    public function test_order_cannot_be_accessed_by_order_no_in_url(): void
    {
        $order = Order::factory()->create();

        $response = $this->get('/order/confirm/'.$order->order_no);

        $response->assertNotFound();
    }

    public function test_confirm_page_shows_awaiting_verification_after_payment_details_submitted(): void
    {
        $order = Order::factory()->paymentDetailsSubmitted()->create([
            'payment_reference' => 'TXN123456',
            'payment_amount' => 749,
        ]);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee(__('Awaiting verification'), false);
        $response->assertSee(__('Payment details received'), false);
        $response->assertSee('TXN123456', false);
        $response->assertSee(__('Update payment details'), false);
        $response->assertDontSee(__('Submit payment details'), false);
        $response->assertDontSee(__('Payment required'), false);
    }

    public function test_submit_payment_redirects_to_confirm_with_submitted_state(): void
    {
        Storage::fake('public');
        $order = Order::factory()->create();

        $response = $this->post(route('order.submit-payment.store', $order), [
            'phone' => $order->guest_phone,
            'payment_reference' => 'REF-999',
            'payment_amount' => $order->amount,
            'payment_made_at' => now()->format('Y-m-d\TH:i'),
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertRedirect(route('order.confirm', $order));
        $response->assertSessionHas('status');
        $response->assertSessionMissing('order_placed');

        $confirm = $this->get(route('order.confirm', $order));
        $confirm->assertOk();
        $confirm->assertSee(__('Payment details received'), false);
        $confirm->assertSee('REF-999', false);
        $confirm->assertDontSee(__('Submit payment details'), false);
    }

    public function test_confirm_page_shows_order_type_and_delivery_address(): void
    {
        $order = Order::factory()->deliveryFulfillment('42 Baker Street, Suite 5')->create([
            'fulfillment_type' => 'delivery',
        ]);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee(__('Order type'), false);
        $response->assertSee(__('Delivery'), false);
        $response->assertSee(__('Delivery address'), false);
        $response->assertSee('42 Baker Street, Suite 5', false);
    }

    public function test_confirm_page_shows_takeaway_without_delivery_address(): void
    {
        $order = Order::factory()->create([
            'fulfillment_type' => 'takeaway',
            'delivery_address' => null,
        ]);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee(__('Take away'), false);
        $response->assertDontSee(__('Delivery address'), false);
    }

    public function test_confirm_page_shows_paid_when_payment_verified(): void
    {
        $order = Order::factory()->verified()->create();

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee(__('Payment verified'), false);
        $response->assertSee(__('Paid'), false);
    }

    public function test_confirm_page_shows_upi_id_and_copy_actions_when_payment_pending(): void
    {
        Storage::fake('public');

        Setting::set('payment_upi_id', 'shop@upi');
        Setting::set('site_name', 'Better Buns');
        Setting::flushCache();

        $order = Order::factory()->create(['amount' => 749.50]);

        $siteSetting = SiteSetting::firstOrCreate([]);
        $siteSetting->addMedia(UploadedFile::fake()->image('payment-qr.jpg'))
            ->toMediaCollection('payment_qr');

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee('shop@upi', false);
        $response->assertSee(__('Copy UPI ID'), false);
        $response->assertSee(__('Download QR code'), false);
        $response->assertSee(route('order.payment-qr.download'), false);
        $response->assertSee(__('Pay with UPI app'), false);
        $response->assertSee('data-upi-pay-button', false);
        $response->assertSee('id="upi-pay-config"', false);
        $response->assertSee(__('Google Pay'), false);
        $response->assertSee(__('PhonePe'), false);
        $response->assertSee('upi://pay?', false);
        $response->assertSee('pa=shop%40upi', false);
        $response->assertSee('am=749.50', false);
        $response->assertSee('tn='.$order->order_no, false);
    }

    public function test_confirm_page_hides_upi_copy_when_payment_details_submitted(): void
    {
        Storage::fake('public');

        Setting::set('payment_upi_id', 'shop@upi');
        Setting::flushCache();

        $siteSetting = SiteSetting::firstOrCreate([]);
        $siteSetting->addMedia(UploadedFile::fake()->image('payment-qr.jpg'))
            ->toMediaCollection('payment_qr');

        $order = Order::factory()->paymentDetailsSubmitted()->create([
            'payment_reference' => 'TXN123456',
            'payment_amount' => 749,
        ]);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertDontSee(__('Copy UPI ID'), false);
        $response->assertDontSee(__('Download QR code'), false);
        $response->assertDontSee(__('Pay with UPI app'), false);
        $response->assertDontSee('data-upi-pay-button', false);
        $response->assertDontSee('id="upi-pay-config"', false);
        $response->assertDontSee('upi://pay?', false);
    }

    public function test_fresh_order_redirect_sets_order_placed_session(): void
    {
        $product = $this->simpleProduct();

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'takeaway',
            'quantity' => 1,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('order_placed', true);
    }

    public function test_payment_qr_download_returns_attachment(): void
    {
        Storage::fake('public');

        $siteSetting = SiteSetting::firstOrCreate([]);
        $siteSetting->addMedia(UploadedFile::fake()->image('payment-qr.jpg'))
            ->toMediaCollection('payment_qr');

        $response = $this->get(route('order.payment-qr.download'));

        $response->assertOk();
        $response->assertDownload('payment-qr.jpg');
    }

    /**
     * @return array<string, mixed>
     */
    private function validOrderPayload(): array
    {
        $rules = app(OrderService::class)->deliveryAtRules();
        $delivery = Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');

        return [
            'guest_name' => 'Test Customer',
            'guest_email' => 'customer@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $delivery,
        ];
    }

    private function simpleProduct(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
        ]);
    }
}
