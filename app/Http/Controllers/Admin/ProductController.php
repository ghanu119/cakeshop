<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\VariantOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private VariantOptionService $variantOptionService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Product::class);
        $products = $this->productService->list(request());
        $categories = Category::orderBy('name_en')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        $product = null;
        $categories = Category::active()->orderBy('name_en')->get();
        $weightValues = $this->variantOptionService->activeWeightValues();

        return view('admin.products.create', compact('product', 'categories', 'weightValues'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->createOrUpdate(null, $request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product_images');
        }

        return redirect()->route('admin.products.index')->with('status', __('Product created.'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        $product->load(['media', 'variants.selections.value', 'variants.selections.type']);
        $categories = Category::active()->orderBy('name_en')->get();
        $weightValues = $this->variantOptionService->activeWeightValues();

        return view('admin.products.edit', compact('product', 'categories', 'weightValues'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->createOrUpdate($product, $request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_images');
            $product->addMediaFromRequest('image')->toMediaCollection('product_images');
        }

        return redirect()->route('admin.products.index')->with('status', __('Product updated.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', __('Product deleted.'));
    }
}
