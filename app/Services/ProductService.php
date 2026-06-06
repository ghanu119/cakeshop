<?php

namespace App\Services;

use App\Models\Flavor;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductService
{
    private const ADMIN_SORTABLE_COLUMNS = [
        'name_en',
        'category',
        'price',
        'status',
        'show_on_homepage',
        'updated_at',
    ];

    public function __construct(
        private ProductVariantService $productVariantService
    ) {}

    public function list(Request $request): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'media']);

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name_en', 'like', "%{$term}%")
                    ->orWhere('name_hi', 'like', "%{$term}%")
                    ->orWhere('name_gu', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $this->applyAdminListSorting($query, $request);

        return $query->paginate(15)->withQueryString();
    }

    private function applyAdminListSorting(Builder $query, Request $request): void
    {
        $sort = $request->input('sort', 'name_en');
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, self::ADMIN_SORTABLE_COLUMNS, true)) {
            $sort = 'name_en';
        }

        if ($sort === 'category') {
            $query->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->select('products.*')
                ->orderBy('categories.name_en', $direction);

            return;
        }

        $query->orderBy($sort, $direction);
    }

    public function listForHomepage(Request $request): LengthAwarePaginator
    {
        $query = Product::query()->with([
            'category',
            'media',
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ])->active();

        $this->applyStorefrontFilters($query, $request);

        $sort = $request->input('sort', 'name_asc');
        match ($sort) {
            'name_desc' => $query->orderByDesc('name_en'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('name_en'),
        };

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @return array{flavors: \Illuminate\Database\Eloquent\Collection, weights: \Illuminate\Database\Eloquent\Collection}
     */
    public function filterOptions(): array
    {
        $flavors = Flavor::query()
            ->active()
            ->whereHas('products', fn ($q) => $q->active())
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        $weights = VariantOptionValue::query()
            ->active()
            ->forTypeSlug('weight')
            ->whereIn('id', function ($query) {
                $query->select('product_variant_selections.variant_option_value_id')
                    ->from('product_variant_selections')
                    ->join('product_variants', 'product_variants.id', '=', 'product_variant_selections.product_variant_id')
                    ->join('products', 'products.id', '=', 'product_variants.product_id')
                    ->where('product_variants.status', 'active')
                    ->whereNull('product_variants.deleted_at')
                    ->where('products.status', 'active')
                    ->whereNull('products.deleted_at');
            })
            ->orderBy('grams')
            ->orderBy('sort_order')
            ->get();

        return [
            'flavors' => $flavors,
            'weights' => $weights,
        ];
    }

    public function catalogPriceRange(): ?object
    {
        $variantBounds = ProductVariant::query()
            ->active()
            ->selectRaw('product_id, MIN(price) as min_price, MAX(price) as max_price')
            ->groupBy('product_id')
            ->get();

        $basePrices = Product::query()
            ->active()
            ->whereDoesntHave('variants', fn ($q) => $q->active())
            ->pluck('price');

        $allMins = $variantBounds->pluck('min_price')->merge($basePrices);
        $allMaxs = $variantBounds->pluck('max_price')->merge($basePrices);

        if ($allMins->isEmpty()) {
            return null;
        }

        return (object) [
            'min_price' => (float) $allMins->min(),
            'max_price' => (float) $allMaxs->max(),
        ];
    }

    private function applyStorefrontFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name_en', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description_en', 'like', "%{$term}%")
                    ->orWhere('ingredients', 'like', "%{$term}%")
                    ->orWhereHas('flavors', function ($fq) use ($term) {
                        $fq->active()->where(function ($fq2) use ($term) {
                            $fq2->where('name_en', 'like', "%{$term}%")
                                ->orWhere('name_hi', 'like', "%{$term}%")
                                ->orWhere('name_gu', 'like', "%{$term}%");
                        });
                    })
                    ->orWhereHas('variants', function ($vq) use ($term) {
                        $vq->active()->whereHas('selections', function ($sq) use ($term) {
                            $sq->whereHas('value', fn ($val) => $val->where('label', 'like', "%{$term}%"));
                        });
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $flavorIds = $request->input('flavor_ids', []);
        if (is_array($flavorIds) && $flavorIds !== []) {
            $query->withFlavorIds($flavorIds);
        }

        $weightIds = $request->input('weight_ids', []);
        if (is_array($weightIds) && $weightIds !== []) {
            $query->withWeightValueIds($weightIds);
        }

        $priceMin = $request->filled('price_min') && is_numeric($request->input('price_min'))
            ? (float) $request->input('price_min')
            : null;
        $priceMax = $request->filled('price_max') && is_numeric($request->input('price_max'))
            ? (float) $request->input('price_max')
            : null;

        if ($priceMin !== null || $priceMax !== null) {
            $query->priceInRange($priceMin, $priceMax);
        }
    }

    public function createOrUpdate(?Product $product, array $data): Product
    {
        $product = $product ?? new Product;

        $product->category_id = $data['category_id'];
        $product->name_en = $data['name_en'];
        $product->name_hi = $data['name_hi'] ?? null;
        $product->name_gu = $data['name_gu'] ?? null;
        $product->description_en = $data['description_en'] ?? null;
        $product->description_hi = $data['description_hi'] ?? null;
        $product->description_gu = $data['description_gu'] ?? null;
        $product->ingredients = $data['ingredients'] ?? null;
        $product->short_description = $data['short_description'] ?? null;
        $product->message_on_cake_max_length = isset($data['message_on_cake_max_length']) && $data['message_on_cake_max_length'] !== '' && $data['message_on_cake_max_length'] !== null
            ? (int) $data['message_on_cake_max_length']
            : null;
        if (! empty($data['variants'])) {
            $product->price = $data['price'] ?? 0;
        } else {
            $product->price = $data['price'];
        }
        $product->status = $data['status'] ?? 'active';
        $product->meta_title = $data['meta_title'] ?? null;
        $product->meta_description = $data['meta_description'] ?? null;
        $product->show_on_homepage = ! empty($data['show_on_homepage']);
        $product->is_highlight = ! empty($data['is_highlight']);
        $product->is_trending = ! empty($data['is_trending']);
        $product->is_featured = ! empty($data['is_featured']);
        $product->homepage_sort_order = isset($data['homepage_sort_order']) && $data['homepage_sort_order'] !== '' ? (int) $data['homepage_sort_order'] : null;

        $slugBase = Str::slug($data['name_en']);
        $slug = $slugBase;
        $count = 0;
        while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
            $slug = $slugBase.'-'.(++$count);
        }
        $product->slug = $slug;

        $product->save();

        if (! empty($data['variants'])) {
            $this->productVariantService->syncVariants($product, $data['variants']);
        } else {
            $product->variants()->each(fn ($v) => $v->delete());
        }

        $this->syncFlavors($product, $data['flavor_ids'] ?? []);

        return $product->fresh();
    }

    public function syncFlavors(Product $product, array $flavorIds): void
    {
        $sync = collect($flavorIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values()
            ->mapWithKeys(fn ($id, $index) => [(int) $id => ['sort_order' => $index]])
            ->all();

        $product->flavors()->sync($sync);
    }
}
