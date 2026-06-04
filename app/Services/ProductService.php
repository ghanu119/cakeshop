<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductService
{
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

        return $query->orderBy('name_en')->paginate(15)->withQueryString();
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

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name_en', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description_en', 'like', "%{$term}%")
                    ->orWhere('ingredients', 'like', "%{$term}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('price_min') && is_numeric($request->input('price_min'))) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max') && is_numeric($request->input('price_max'))) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

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
            $slug = $slugBase . '-' . (++$count);
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
