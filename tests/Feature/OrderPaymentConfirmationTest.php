<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_page_shows_submit_cta_when_no_payment_details(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee($order->order_no, false);
        $response->assertSee(__('Payment required'), false);
        $response->assertSee(__('Submit payment details'), false);
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
}
