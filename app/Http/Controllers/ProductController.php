<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListProductsRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductVariantService $productVariantService
    ) {}

    public function index(ListProductsRequest $request, ?string $slug = null): View|RedirectResponse
    {
        $category = null;

        if ($slug !== null) {
            $category = Category::where('slug', $slug)->active()->first();

            if (! $category) {
                if (Product::where('slug', $slug)->active()->exists()) {
                    return redirect()->route('product.show', $slug, 301);
                }

                abort(404);
            }
        }

        if ($category === null && $request->filled('category_id') && ! $this->hasNonCategoryCatalogFilters($request)) {
            $filterCategory = Category::active()->find($request->integer('category_id'));

            if ($filterCategory) {
                return redirect()->route('products.category', array_filter([
                    'slug' => $filterCategory->slug,
                    'sort' => $request->input('sort'),
                    'page' => $request->input('page'),
                ]), 301);
            }
        }

        $products = $category
            ? $this->productService->listForCategory($category, $request)
            : $this->productService->listForHomepage($request);

        $categories = Category::active()->orderBy('sort_order')->get();
        $priceRange = $this->productService->catalogPriceRange();
        $filterOptions = $this->productService->filterOptions();
        $filterFlavors = $filterOptions['flavors'];
        $filterWeights = $filterOptions['weights'];

        return view('products.index', compact(
            'products',
            'categories',
            'priceRange',
            'filterFlavors',
            'filterWeights',
            'category',
        ));
    }

    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)->active()->with('category')->firstOrFail();
        $product->load([
            'media',
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ]);
        $this->productVariantService->eagerLoadForStorefront($product);
        $variantChoices = $this->productVariantService->choicesForProduct($product);
        $defaultVariant = $this->productVariantService->defaultVariant($product);
        $hasVariants = $this->productVariantService->hasVariants($product);

        $related = $this->getRelatedProducts($product, 4);

        return view('products.show', compact(
            'product',
            'related',
            'variantChoices',
            'defaultVariant',
            'hasVariants'
        ));
    }

    /**
     * Relations needed for storefront product cards (matches homepage/catalog listings).
     *
     * @return array<string, mixed>
     */
    private function relatedProductCardRelations(): array
    {
        return [
            'category',
            'media',
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
        ];
    }

    /**
     * Related products: same category (random), then fill up to limit with others if needed.
     */
    private function getRelatedProducts(Product $product, int $limit = 4)
    {
        $with = $this->relatedProductCardRelations();

        $related = Product::related($product, $limit)->with($with)->get();

        if ($related->count() >= $limit) {
            return $related;
        }

        $excludeIds = $related->pluck('id')->all();
        $fill = Product::exceptIds($product, $excludeIds)
            ->with($with)
            ->limit($limit - $related->count())
            ->get();

        return $related->merge($fill);
    }

    private function hasNonCategoryCatalogFilters(ListProductsRequest $request): bool
    {
        return $request->filled('search')
            || $request->filled('price_min')
            || $request->filled('price_max')
            || $request->filled('flavor_ids')
            || $request->filled('weight_ids');
    }
}
