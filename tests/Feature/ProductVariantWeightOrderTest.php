<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\VariantOptionValue;
use App\Services\ProductVariantService;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantWeightOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VariantOptionSeeder::class);
    }

    public function test_storefront_lists_weights_from_lowest_to_highest(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();
        $weight2kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 2000)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Weight Order Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight2kg->id, 'price' => 2499],
            ['variant_option_value_id' => $weight1kg->id, 'price' => 1599],
            ['variant_option_value_id' => $weight500->id, 'price' => 899],
        ]);

        $choices = app(ProductVariantService::class)->choicesForProduct($product->fresh());
        $defaultVariant = app(ProductVariantService::class)->defaultVariant($product->fresh());

        $this->assertSame([500, 1000, 2000], $choices->pluck('grams')->all());
        $this->assertSame($weight500->id, $defaultVariant?->selections->first()?->variant_option_value_id);
    }

    public function test_product_page_renders_lowest_weight_first_and_selected(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();
        $weight2kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 2000)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Weight Order Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight2kg->id, 'price' => 2499],
            ['variant_option_value_id' => $weight1kg->id, 'price' => 1599],
            ['variant_option_value_id' => $weight500->id, 'price' => 899],
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSeeInOrder(['500 gm', '1 KG', '2 KG'], false);

        $defaultVariant = app(ProductVariantService::class)->defaultVariant($product->fresh());
        $response->assertSee('data-initial-variant-id="'.$defaultVariant?->id.'"', false);
        $response->assertSee('aria-pressed="true">500 gm</button>', false);
    }
}
