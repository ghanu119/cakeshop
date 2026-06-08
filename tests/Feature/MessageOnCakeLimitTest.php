<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageOnCakeLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('message_on_cake_max_length', '30');
        Setting::flushCache();
    }

    public function test_order_rejects_message_longer_than_site_default(): void
    {
        $product = $this->createProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'message_on_cake' => str_repeat('a', 31),
        ]));

        $response->assertSessionHasErrors('message_on_cake');
    }

    public function test_order_accepts_message_within_site_default(): void
    {
        $product = $this->createProduct();

        $message = str_repeat('b', 30);
        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'message_on_cake' => $message,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'message_on_cake' => $message,
        ]);
    }

    public function test_product_override_limits_message_length(): void
    {
        $product = $this->createProduct(['message_on_cake_max_length' => 15]);

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'message_on_cake' => str_repeat('c', 16),
        ]));

        $response->assertSessionHasErrors('message_on_cake');
    }

    public function test_product_resolves_limit_when_override_set(): void
    {
        $product = $this->createProduct(['message_on_cake_max_length' => 20]);

        $this->assertSame(20, $product->messageOnCakeMaxLength());
    }

    private function createProduct(array $overrides = []): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'price' => 500,
        ], $overrides));
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
            'guest_name' => 'Test Guest',
            'guest_email' => 'customer@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $delivery,
            'fulfillment_type' => 'takeaway',
        ];
    }
}
