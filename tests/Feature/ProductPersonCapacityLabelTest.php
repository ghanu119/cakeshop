<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\VariantOptionValue;
use App\Services\ProductVariantService;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPersonCapacityLabelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VariantOptionSeeder::class);
    }

    public function test_product_page_shows_person_capacity_label_for_default_weight(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Capacity Test Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 899, 'is_default' => true],
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('4 - 5 People', false);
        $response->assertSee('data-variant-capacity', false);
    }

    public function test_product_page_includes_capacity_label_in_variant_choices_json(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Multi Weight Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 899, 'is_default' => true],
            ['variant_option_value_id' => $weight1kg->id, 'price' => 1599],
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('person_capacity_label', false);
        $response->assertSee('4 - 5 People', false);
        $response->assertSee('8 - 10 People', false);
    }
}
