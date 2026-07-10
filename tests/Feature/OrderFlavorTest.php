<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlavorTest extends TestCase
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

    public function test_product_with_flavors_requires_flavor_id_on_order(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $flavor = Flavor::factory()->create(['slug' => 'chocolate', 'name_en' => 'Chocolate']);
        $product->flavors()->attach($flavor->id, ['sort_order' => 0]);

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), $this->validOrderPayload());

        $response->assertSessionHasErrors('flavor_id');
    }

    public function test_order_snapshots_selected_flavor(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 500]);
        $flavor = Flavor::factory()->create(['slug' => 'vanilla', 'name_en' => 'Vanilla']);
        $product->flavors()->attach($flavor->id, ['sort_order' => 0]);

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'flavor_id' => $flavor->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'flavor_id' => $flavor->id,
            'flavor_name' => 'Vanilla',
            'flavor_slug' => 'vanilla',
        ]);
    }

    public function test_product_without_flavors_does_not_require_flavor_id(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 500]);

        $response = $this->actingAs($this->createStorefrontCustomer())->post(route('order.store', $product), $this->validOrderPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'flavor_id' => null,
            'flavor_name' => null,
        ]);
    }

    public function test_order_service_applies_flavor_snapshot(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 750]);
        $flavor = Flavor::factory()->create(['slug' => 'mango', 'name_en' => 'Mango']);
        $product->flavors()->attach($flavor->id, ['sort_order' => 0]);

        $order = app(OrderService::class)->createOrder($product, array_merge($this->validOrderPayload(), [
            'flavor_id' => $flavor->id,
        ]));

        $this->assertSame($flavor->id, $order->flavor_id);
        $this->assertSame('Mango', $order->flavor_name);
        $this->assertSame('mango', $order->flavor_slug);
        $this->assertSame(750.0, (float) $order->amount);
    }

    public function test_place_page_does_not_preselect_flavor(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
        $flavorA = Flavor::factory()->create(['slug' => 'chocolate', 'name_en' => 'Chocolate']);
        $flavorB = Flavor::factory()->create(['slug' => 'vanilla', 'name_en' => 'Vanilla']);
        $product->flavors()->attach($flavorA->id, ['sort_order' => 0]);
        $product->flavors()->attach($flavorB->id, ['sort_order' => 1]);

        $response = $this->actingAs($this->createStorefrontCustomer())
            ->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('data-has-flavors', false);
        $response->assertSee('data-flavor-required', false);
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/id="flavor_id"[^>]*value=""/', $html);
        $this->assertStringNotContainsString(
            'data-flavor-id="'.$flavorA->id.'" aria-pressed="true"',
            preg_replace('/\s+/', ' ', $html),
        );
        $this->assertStringNotContainsString(
            'data-flavor-id="'.$flavorB->id.'" aria-pressed="true"',
            preg_replace('/\s+/', ' ', $html),
        );
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
            'fulfillment_type' => 'takeaway',
            'delivery_at' => $delivery,
        ];
    }
}
