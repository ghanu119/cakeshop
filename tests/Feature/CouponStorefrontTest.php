<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::flushCache();
    }

    public function test_single_auto_apply_coupon_shows_promo_on_product_index(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 1000]);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'AUTO10',
            'label' => 'Summer Sale',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('price-promo-original', false);
        $response->assertSee('price-promo-badge', false);
        $response->assertSee('10% OFF', false);
        $response->assertSee('900.00', false);
        $response->assertSee('1,000.00', false);
        $response->assertDontSee('coupon-max-discount-info', false);
    }

    public function test_two_auto_apply_coupons_show_lowest_discount_promo_on_catalog(): void
    {
        Product::factory()->create(['status' => 'active', 'price' => 1000]);

        Coupon::factory()->autoApply()->percentage(10)->create(['code' => 'A1']);
        Coupon::factory()->autoApply()->percentage(15)->create(['code' => 'A2']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('price-promo-original', false);
        $response->assertSee('price-promo-badge', false);
        $response->assertSee('10% OFF', false);
        $response->assertSee('900.00', false);
        $response->assertSee('1,000.00', false);
        $response->assertDontSee('15% OFF', false);
    }

    public function test_two_auto_apply_coupons_show_lowest_discount_promo_on_product_detail(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 1000]);

        Coupon::factory()->autoApply()->fixed(100)->create(['code' => 'FIX100']);
        Coupon::factory()->autoApply()->percentage(15)->create(['code' => 'PCT15']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('₹100 OFF', false);
        $response->assertSee('900.00', false);
        $response->assertDontSee('15% OFF', false);
    }

    public function test_auto_apply_tie_breaks_to_lowest_coupon_id_when_discount_equal(): void
    {
        Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(100)->create(['code' => 'FIX100']);
        Coupon::factory()->autoApply()->percentage(20)->create(['code' => 'PCT20']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('₹100 OFF', false);
        $response->assertSee('400.00', false);
        $response->assertDontSee('20% OFF', false);
    }

    public function test_min_order_amount_excludes_coupon_and_shows_next_best_promo(): void
    {
        Product::factory()->create(['status' => 'active', 'price' => 300]);

        Coupon::factory()->autoApply()->fixed(100)->create([
            'code' => 'BIG100',
            'min_order_amount' => 500,
        ]);
        Coupon::factory()->autoApply()->fixed(30)->create(['code' => 'SMALL30']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('₹30 OFF', false);
        $response->assertSee('270.00', false);
        $response->assertDontSee('200.00', false);
    }

    public function test_product_below_min_order_amount_has_no_card_promo(): void
    {
        Product::factory()->create(['status' => 'active', 'price' => 100]);

        Coupon::factory()->autoApply()->fixed(20)->create([
            'code' => 'AUTO20',
            'min_order_amount' => 500,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('100.00', false);
        $response->assertDontSee('80.00', false);
    }

    public function test_product_detail_shows_promo_pricing(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 800]);

        Coupon::factory()->autoApply()->percentage(10, 200)->create([
            'code' => 'PDP10',
            'label' => 'Weekend Deal',
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('10% OFF', false);
        $response->assertSee('720.00', false);
        $response->assertDontSee('coupon-max-discount-info', false);
    }

    public function test_better_buns_product_index_shows_strikethrough_and_badge_by_price(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        Product::factory()->create(['status' => 'active', 'price' => 449]);

        Coupon::factory()->autoApply()->percentage(10, 200)->create([
            'code' => 'TEST',
            'label' => 'Auto 10% off',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('price-promo-original', false);
        $response->assertSee('price-promo-badge', false);
        $response->assertSee('449.00', false);
        $response->assertSee('404.10', false);
        $response->assertSee('10% OFF', false);
    }

    public function test_better_buns_product_detail_shows_discounted_price(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = Product::factory()->create(['status' => 'active', 'price' => 999, 'slug' => 'red-velvet-cake-test']);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'TEST',
            'label' => 'Auto 10% off',
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('10% OFF', false);
        $response->assertSee('899.10', false);
        $response->assertSee('999.00', false);
    }
}
