<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredients_page_returns_not_found_for_non_better_buns_theme(): void
    {
        Setting::set('theme', 'warm');
        Setting::flushCache();

        $this->get(route('ingredients'))->assertNotFound();
    }

    public function test_better_buns_ingredients_page_renders_content(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $response = $this->get(route('ingredients'));

        $response->assertOk();
        $response->assertSee(__('Ingredients'), false);
        $response->assertSee(__('you can trust'), false);
        $response->assertSee('data-testid="ingredients-page"', false);
        $response->assertSee('data-testid="ingredients-bento"', false);
        $response->assertSee(__('From sponge to finishing touch'), false);
    }

    public function test_better_buns_ingredients_page_lists_product_highlights(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = Product::factory()->create([
            'ingredients' => "Belgian chocolate\nFresh cream\nVanilla bean",
            'status' => 'active',
        ]);

        $response = $this->get(route('ingredients'));

        $response->assertOk();
        $response->assertSee($product->name_en, false);
        $response->assertSee('Belgian chocolate', false);
        $response->assertSee('data-testid="ingredients-product-list"', false);
    }
}
