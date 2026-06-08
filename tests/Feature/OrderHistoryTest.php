<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_lookup_shows_order_when_order_no_and_phone_match(): void
    {
        $order = Order::factory()->create([
            'guest_phone' => '9876543210',
        ]);

        $response = $this->post(route('order.history.search'), [
            'order_no' => $order->order_no,
            'phone' => $order->guest_phone,
        ]);

        $response->assertOk();
        $response->assertSee($order->order_no, false);
        $response->assertSee($order->displayProductName(), false);
        $response->assertSee(__('View order'), false);
        $response->assertDontSee(__('The order reference or phone number does not match our records.'), false);
    }

    public function test_history_lookup_fails_when_order_no_does_not_match(): void
    {
        $order = Order::factory()->create([
            'guest_phone' => '9876543210',
        ]);

        $response = $this->from(route('order.history'))
            ->post(route('order.history.search'), [
                'order_no' => 'BB-99999999-999',
                'phone' => $order->guest_phone,
            ]);

        $response->assertRedirect(route('order.history'));
        $response->assertSessionHasErrors('phone');
        $response->assertSessionHasInput('order_no', 'BB-99999999-999');
        $response->assertSessionHasInput('phone', $order->guest_phone);
    }

    public function test_history_lookup_fails_when_phone_does_not_match(): void
    {
        $order = Order::factory()->create([
            'guest_phone' => '9876543210',
        ]);

        $response = $this->from(route('order.history'))
            ->post(route('order.history.search'), [
                'order_no' => $order->order_no,
                'phone' => '0000000000',
            ]);

        $response->assertRedirect(route('order.history'));
        $response->assertSessionHasErrors('phone');
        $response->assertSessionHasInput('order_no', $order->order_no);
        $response->assertSessionHasInput('phone', '0000000000');
    }

    public function test_history_lookup_requires_order_no_and_phone(): void
    {
        $response = $this->from(route('order.history'))
            ->post(route('order.history.search'), []);

        $response->assertRedirect(route('order.history'));
        $response->assertSessionHasErrors(['order_no', 'phone']);
    }

    public function test_history_form_shows_order_reference_and_phone_fields(): void
    {
        $response = $this->get(route('order.history'));

        $response->assertOk();
        $response->assertSee(__('Order reference'), false);
        $response->assertSee(__('Phone number'), false);
        $response->assertSee(__('Look up order'), false);
    }
}
