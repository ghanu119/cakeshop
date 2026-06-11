<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use App\Services\ProductVariantService;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VariantOptionSeeder::class);
    }

    public function test_sync_variants_restores_soft_deleted_variant_with_same_weight(): void
    {
        $product = Product::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 500],
        ]);

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->firstOrFail();
        $variantId = $variant->id;
        $selectionHash = $variant->selection_hash;

        app(ProductVariantService::class)->syncVariants($product, []);

        $this->assertSoftDeleted('product_variants', ['id' => $variantId]);

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 550],
        ]);

        $restored = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('selection_hash', $selectionHash)
            ->firstOrFail();

        $this->assertSame($variantId, $restored->id);
        $this->assertNull($restored->deleted_at);
        $this->assertSame('550.00', $restored->price);
        $this->assertSame(1, ProductVariant::query()->where('product_id', $product->id)->count());
    }
}
