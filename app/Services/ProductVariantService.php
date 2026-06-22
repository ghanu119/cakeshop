<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderVariantSelection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantSelection;
use App\Models\VariantOptionType;
use App\Models\VariantOptionValue;
use Illuminate\Support\Collection;
class ProductVariantService
{
    public function hasVariants(Product $product): bool
    {
        if ($product->relationLoaded('variants')) {
            return $product->variants->where('status', 'active')->isNotEmpty();
        }

        return $product->variants()->active()->exists();
    }

    public function computeSelectionHash(array $valueIds): string
    {
        $ids = array_map('intval', $valueIds);
        sort($ids);

        return hash('sha256', implode(',', $ids));
    }

    public function syncVariants(Product $product, array $rows): void
    {
        $weightType = VariantOptionType::query()->slug('weight')->first();
        if (! $weightType) {
            return;
        }

        $existingIds = [];
        $sortOrder = 0;
        $isFirst = true;

        foreach ($rows as $row) {
            $valueId = (int) ($row['variant_option_value_id'] ?? 0);
            $price = (float) ($row['price'] ?? 0);
            if ($valueId <= 0) {
                continue;
            }

            $value = VariantOptionValue::query()
                ->where('id', $valueId)
                ->where('variant_option_type_id', $weightType->id)
                ->active()
                ->first();
            if (! $value) {
                continue;
            }

            $hash = $this->computeSelectionHash([$valueId]);
            $variantId = isset($row['id']) ? (int) $row['id'] : null;

            $variant = null;
            if ($variantId) {
                $variant = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('product_id', $product->id)
                    ->first();
            }

            if (! $variant) {
                $variant = ProductVariant::withTrashed()
                    ->where('product_id', $product->id)
                    ->where('selection_hash', $hash)
                    ->first();
            }

            if ($variant?->trashed()) {
                $variant->restore();
            }

            if (! $variant) {
                $variant = new ProductVariant;
                $variant->product_id = $product->id;
            }

            $variant->price = $price;
            $variant->selection_hash = $hash;
            $variant->sort_order = $sortOrder++;
            $variant->is_default = $isFirst;
            $variant->status = 'active';
            $variant->save();
            $isFirst = false;

            $variant->selections()->delete();
            ProductVariantSelection::create([
                'product_variant_id' => $variant->id,
                'variant_option_type_id' => $weightType->id,
                'variant_option_value_id' => $value->id,
            ]);

            $existingIds[] = $variant->id;
        }

        ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereNotIn('id', $existingIds)
            ->each(fn (ProductVariant $v) => $v->delete());

        $product->syncStartingPrice();
    }

    public function syncStartingPrice(Product $product): void
    {
        $min = $product->variants()->active()->min('price');
        if ($min !== null) {
            $product->price = $min;
            $product->saveQuietly();
        }
    }

    public function findVariantForProduct(Product $product, int $productVariantId): ProductVariant
    {
        return ProductVariant::query()
            ->where('id', $productVariantId)
            ->where('product_id', $product->id)
            ->active()
            ->with(['selections.value.type'])
            ->firstOrFail();
    }

    public function buildSummary(ProductVariant $variant): string
    {
        $labels = $variant->selections
            ->load(['value', 'type'])
            ->sortBy(fn ($s) => $s->type?->sort_order ?? 0)
            ->map(fn ($s) => $s->value?->label)
            ->filter()
            ->values();

        return $labels->implode(' · ');
    }

    public function snapshotOrder(Product $product, ProductVariant $variant, Order $order): void
    {
        $variant->load(['selections.value', 'selections.type']);

        $summary = $this->buildSummary($variant);
        $weightGrams = null;

        foreach ($variant->selections as $selection) {
            if ($selection->type?->slug === 'weight') {
                $weightGrams = $selection->value?->grams;
            }
        }

        $order->variant_summary = $summary ?: null;
        $order->weight_grams = $weightGrams;
        $order->save();

        foreach ($variant->selections as $selection) {
            OrderVariantSelection::create([
                'order_id' => $order->id,
                'variant_option_type_id' => $selection->variant_option_type_id,
                'variant_option_type_slug' => $selection->type?->slug ?? 'unknown',
                'variant_option_value_id' => $selection->variant_option_value_id,
                'label' => $selection->value?->label ?? '',
                'grams' => $selection->value?->grams,
            ]);
        }
    }

    /**
     * @return Collection<int, array{id: int, label: string, price: float, grams: ?int, is_default: bool, person_capacity_label: ?string}>
     */
    public function choicesForProduct(Product $product): Collection
    {
        $variants = $product->variants()
            ->active()
            ->with(['selections.value.type'])
            ->orderBy('sort_order')
            ->get();

        return $variants->map(function (ProductVariant $variant) {
            $weightSelection = $variant->selections->first(fn ($s) => $s->type?->slug === 'weight');

            return [
                'id' => $variant->id,
                'label' => $weightSelection?->value?->label ?? $this->buildSummary($variant),
                'price' => (float) $variant->price,
                'grams' => $weightSelection?->value?->grams,
                'is_default' => (bool) $variant->is_default,
                'person_capacity_label' => $weightSelection?->value?->person_capacity_label,
            ];
        })->values();
    }

    public function defaultVariant(Product $product): ?ProductVariant
    {
        return $product->variants()
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->first();
    }

    public function eagerLoadForStorefront(Product $product): Product
    {
        $product->load([
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
            'variants.selections.type',
        ]);

        return $product;
    }
}
