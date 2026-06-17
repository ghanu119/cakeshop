<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_renders_only_products_in_category(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $cakes = Category::factory()->create(['name_en' => 'Birthday Cakes', 'slug' => 'birthday-cakes']);
        $pastries = Category::factory()->create(['name_en' => 'Pastries', 'slug' => 'pastries']);

        Product::factory()->create([
            'category_id' => $cakes->id,
            'name_en' => 'Chocolate Birthday Cake',
            'status' => 'active',
        ]);
        Product::factory()->create([
            'category_id' => $pastries->id,
            'name_en' => 'Butter Croissant',
            'status' => 'active',
        ]);

        $response = $this->get(route('products.category', $cakes->slug));

        $response->assertOk();
        $response->assertSee('data-testid="category-page"', false);
        $response->assertSee('Chocolate Birthday Cake', false);
        $response->assertDontSee('Butter Croissant', false);
        $response->assertSee('<link rel="canonical" href="' . route('products.category', $cakes->slug) . '"', false);
        $response->assertSee('CollectionPage', false);
    }

    public function test_legacy_categories_url_redirects_to_products_category(): void
    {
        $category = Category::factory()->create(['slug' => 'wedding-cakes']);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertRedirect(route('products.category', $category->slug));
    }

    public function test_legacy_products_url_redirects_to_product_show_when_slug_is_product(): void
    {
        $product = Product::factory()->create([
            'slug' => 'chocolate-truffle-cake',
            'status' => 'active',
        ]);

        $response = $this->get('/products/' . $product->slug);

        $response->assertRedirect(route('product.show', $product->slug));
    }

    public function test_products_index_redirects_bare_category_id_filter_to_category_page(): void
    {
        $category = Category::factory()->create(['slug' => 'wedding-cakes']);

        $response = $this->get(route('products.index', ['category_id' => $category->id]));

        $response->assertRedirect(route('products.category', ['slug' => $category->slug]));
    }

    public function test_products_index_keeps_category_id_when_other_filters_present(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('products.index', [
            'category_id' => $category->id,
            'search' => 'chocolate',
        ]));

        $response->assertOk();
        $response->assertSee('name="search"', false);
    }

    public function test_inactive_category_page_returns_not_found(): void
    {
        $category = Category::factory()->create(['status' => 'inactive']);

        $this->get(route('products.category', $category->slug))->assertNotFound();
    }

    public function test_category_menu_appears_on_all_storefront_pages(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        Category::factory()->create(['name_en' => 'Birthday Cakes', 'slug' => 'birthday-cakes']);

        $response = $this->get(route('contact.index'));

        $response->assertOk();
        $response->assertSee('data-testid="category-pills-bar"', false);
        $response->assertSee('Birthday Cakes', false);
    }
}
