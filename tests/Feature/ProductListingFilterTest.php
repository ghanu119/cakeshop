<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\Setting;
use App\Models\VariantOptionValue;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VariantOptionSeeder::class);
    }

    public function test_flavor_ids_filter_returns_matching_products_only(): void
    {
        $category = Category::factory()->create();
        $chocolate = Flavor::factory()->create(['name_en' => 'Chocolate Cake Flavor', 'slug' => 'chocolate-filter']);
        $vanilla = Flavor::factory()->create(['name_en' => 'Vanilla Cake Flavor', 'slug' => 'vanilla-filter']);

        $withChocolate = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Chocolate Layer Cake',
            'status' => 'active',
        ]);
        $withVanilla = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Vanilla Sponge Cake',
            'status' => 'active',
        ]);

        app(ProductService::class)->syncFlavors($withChocolate, [$chocolate->id]);
        app(ProductService::class)->syncFlavors($withVanilla, [$vanilla->id]);

        $response = $this->get(route('products.index', ['flavor_ids' => [$chocolate->id]]));

        $response->assertOk();
        $response->assertSee('Chocolate Layer Cake', false);
        $response->assertDontSee('Vanilla Sponge Cake', false);
    }

    public function test_multiple_flavor_ids_use_or_semantics(): void
    {
        $category = Category::factory()->create();
        $flavorA = Flavor::factory()->create(['slug' => 'flavor-a-or']);
        $flavorB = Flavor::factory()->create(['slug' => 'flavor-b-or']);

        $productA = Product::factory()->create(['category_id' => $category->id, 'name_en' => 'Product Alpha Filter', 'status' => 'active']);
        $productB = Product::factory()->create(['category_id' => $category->id, 'name_en' => 'Product Beta Filter', 'status' => 'active']);
        $productC = Product::factory()->create(['category_id' => $category->id, 'name_en' => 'Product Gamma Filter', 'status' => 'active']);

        app(ProductService::class)->syncFlavors($productA, [$flavorA->id]);
        app(ProductService::class)->syncFlavors($productB, [$flavorB->id]);

        $response = $this->get(route('products.index', ['flavor_ids' => [$flavorA->id, $flavorB->id]]));

        $response->assertOk();
        $response->assertSee('Product Alpha Filter', false);
        $response->assertSee('Product Beta Filter', false);
        $response->assertDontSee('Product Gamma Filter', false);
    }

    public function test_weight_ids_filter_returns_products_with_matching_variant(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();

        $halfKgCake = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Half Kg Celebration Cake',
            'status' => 'active',
        ]);
        $oneKgCake = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'One Kg Party Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($halfKgCake, [
            ['variant_option_value_id' => $weight500->id, 'price' => 600],
        ]);
        app(ProductVariantService::class)->syncVariants($oneKgCake, [
            ['variant_option_value_id' => $weight1kg->id, 'price' => 1200],
        ]);

        $response = $this->get(route('products.index', ['weight_ids' => [$weight500->id]]));

        $response->assertOk();
        $response->assertSee('Half Kg Celebration Cake', false);
        $response->assertDontSee('One Kg Party Cake', false);
    }

    public function test_combined_flavor_and_weight_filters_use_and_semantics(): void
    {
        $category = Category::factory()->create();
        $flavor = Flavor::factory()->create(['slug' => 'combo-flavor']);
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();

        $match = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Combo Match Cake',
            'status' => 'active',
        ]);
        $wrongWeight = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Combo Wrong Weight Cake',
            'status' => 'active',
        ]);

        app(ProductService::class)->syncFlavors($match, [$flavor->id]);
        app(ProductService::class)->syncFlavors($wrongWeight, [$flavor->id]);

        app(ProductVariantService::class)->syncVariants($match, [
            ['variant_option_value_id' => $weight500->id, 'price' => 800],
        ]);
        app(ProductVariantService::class)->syncVariants($wrongWeight, [
            ['variant_option_value_id' => $weight1kg->id, 'price' => 900],
        ]);

        $response = $this->get(route('products.index', [
            'flavor_ids' => [$flavor->id],
            'weight_ids' => [$weight500->id],
        ]));

        $response->assertOk();
        $response->assertSee('Combo Match Cake', false);
        $response->assertDontSee('Combo Wrong Weight Cake', false);
    }

    public function test_price_min_matches_higher_variant_not_only_starting_price(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Tiered Price Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 500],
            ['variant_option_value_id' => $weight1kg->id, 'price' => 2000],
        ]);
        $product->refresh();

        $this->assertEquals(500.0, (float) $product->price);

        $response = $this->get(route('products.index', ['price_min' => 1500]));

        $response->assertOk();
        $response->assertSee('Tiered Price Cake', false);
    }

    public function test_price_max_excludes_product_when_all_variants_above_max(): void
    {
        $category = Category::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();
        $weight1kg = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 1000)->firstOrFail();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Premium Only Cake',
            'status' => 'active',
        ]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 1500],
            ['variant_option_value_id' => $weight1kg->id, 'price' => 3000],
        ]);

        $response = $this->get(route('products.index', ['price_max' => 1000]));

        $response->assertOk();
        $response->assertDontSee('Premium Only Cake', false);
    }

    public function test_price_range_uses_base_price_when_product_has_no_variants(): void
    {
        $category = Category::factory()->create();

        $cheap = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Simple Cheap Cake',
            'price' => 250,
            'status' => 'active',
        ]);
        $expensive = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Simple Expensive Cake',
            'price' => 3500,
            'status' => 'active',
        ]);

        $response = $this->get(route('products.index', [
            'price_min' => 200,
            'price_max' => 500,
        ]));

        $response->assertOk();
        $response->assertSee('Simple Cheap Cake', false);
        $response->assertDontSee('Simple Expensive Cake', false);
    }

    public function test_invalid_flavor_id_returns_validation_error(): void
    {
        $response = $this->get(route('products.index', ['flavor_ids' => [99999]]));

        $response->assertSessionHasErrors('flavor_ids.0');
    }

    public function test_category_id_filter_returns_matching_products_only(): void
    {
        $cakes = Category::factory()->create(['name_en' => 'Cakes', 'slug' => 'cakes-filter']);
        $pastries = Category::factory()->create(['name_en' => 'Pastries', 'slug' => 'pastries-filter']);

        Product::factory()->create([
            'category_id' => $cakes->id,
            'name_en' => 'Chocolate Layer Cake',
            'status' => 'active',
        ]);
        Product::factory()->create([
            'category_id' => $pastries->id,
            'name_en' => 'Butter Croissant',
            'status' => 'active',
        ]);

        $response = $this->get(route('products.index', ['category_id' => $cakes->id]));

        $response->assertRedirect(route('products.category', ['slug' => $cakes->slug]));

        $categoryPage = $this->get(route('products.category', $cakes->slug));
        $categoryPage->assertOk();
        $categoryPage->assertSee('Chocolate Layer Cake', false);
        $categoryPage->assertDontSee('Butter Croissant', false);
    }

    public function test_product_index_supports_autoloading_pagination_response(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $category = Category::factory()->create();

        Product::factory()->count(13)->sequence(
            fn ($sequence) => [
                'category_id' => $category->id,
                'name_en' => 'Autoload Product '.str_pad((string) ($sequence->index + 1), 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]
        )->create();

        $response = $this->get(route('products.index', [
            'page' => 2,
            'autoload' => 1,
        ]), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJson([
            'has_more_pages' => false,
            'next_page_url' => null,
        ]);
        $response->assertJsonPath('html', fn (string $html) => str_contains($html, 'Autoload Product 13'));
    }
}
