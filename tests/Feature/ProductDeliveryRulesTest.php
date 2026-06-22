<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeliveryRulesTest extends TestCase
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

    public function test_product_min_hours_override_delivery_rules(): void
    {
        $product = $this->createProduct(['min_hours_before_delivery' => 24]);

        $rules = app(OrderService::class)->deliveryAtRules($product);
        $defaultRules = app(OrderService::class)->deliveryAtRules();

        $productMin = Carbon::parse($rules['after'])->setTimezone($rules['timezone']);
        $defaultMin = Carbon::parse($defaultRules['after'])->setTimezone($defaultRules['timezone']);

        $this->assertTrue($productMin->greaterThan($defaultMin));
        $this->assertSame(24, $product->minHoursBeforeDelivery());
    }

    public function test_suggested_delivery_at_rounds_up_to_fifteen_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:07:00', 'Asia/Kolkata'));

        $product = $this->createProduct(['min_hours_before_delivery' => 4]);
        $orderService = app(OrderService::class);
        $rules = $orderService->deliveryAtRules($product);
        $suggested = $orderService->suggestedDeliveryAt($rules);

        $this->assertSame('2026-06-22T14:15', $suggested);

        Carbon::setTestNow();
    }

    public function test_place_form_prefills_suggested_delivery_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:00:00', 'Asia/Kolkata'));

        $product = $this->createProduct(['min_hours_before_delivery' => 4]);

        $response = $this->actingAs($this->createStorefrontCustomer())
            ->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('value="2026-06-22T14:00"', false);

        Carbon::setTestNow();
    }

    public function test_order_rejects_delivery_before_product_min_hours(): void
    {
        $product = $this->createProduct(['min_hours_before_delivery' => 24]);

        $tooSoon = Carbon::now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i');

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), array_merge($this->validOrderPayload($product), [
            'delivery_at' => $tooSoon,
        ]));

        $response->assertSessionHasErrors('delivery_at');
    }

    public function test_order_rejects_delivery_before_minimum_in_site_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 18:00:00', 'Asia/Kolkata'));

        $product = $this->createProduct(['min_hours_before_delivery' => 4]);
        $orderService = app(OrderService::class);
        $rules = $orderService->deliveryAtRules($product);
        $bounds = $orderService->deliveryAtBoundsForInput($rules);

        // 8 PM local is before the 10 PM minimum (18:00 + 4h), but would incorrectly pass UTC-based rules.
        $tooSoon = '2026-06-22T20:00';

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), array_merge($this->validOrderPayload($product), [
            'delivery_at' => $tooSoon,
        ]));

        $response->assertSessionHasErrors('delivery_at');
        $this->assertSame('2026-06-22T22:00', $bounds['min']);

        Carbon::setTestNow();
    }

    public function test_order_accepts_delivery_at_minimum_in_site_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 18:00:00', 'Asia/Kolkata'));

        $product = $this->createProduct(['min_hours_before_delivery' => 4]);
        $bounds = app(OrderService::class)->deliveryAtBoundsForInput(
            app(OrderService::class)->deliveryAtRules($product)
        );

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), array_merge($this->validOrderPayload($product), [
            'delivery_at' => $bounds['min'],
        ]));

        $response->assertRedirect();

        Carbon::setTestNow();
    }

    public function test_order_snapshots_product_sku(): void
    {
        $product = $this->createProduct([
            'sku' => 'TEST-SKU-001',
            'min_hours_before_delivery' => 4,
        ]);

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), $this->validOrderPayload($product));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'product_sku' => 'TEST-SKU-001',
        ]);
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
    private function validOrderPayload(Product $product): array
    {
        $rules = app(OrderService::class)->deliveryAtRules($product);
        $delivery = app(OrderService::class)->suggestedDeliveryAt($rules);

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
