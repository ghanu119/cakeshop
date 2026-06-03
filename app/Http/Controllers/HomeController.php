<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Models\Testimonial;
use App\Services\ProductService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(): View
    {
        $with = [
            'media',
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ];
        $highlights = Product::highlight()->with($with)->limit(8)->get();
        $trending = Product::trending()->with($with)->limit(8)->get();
        $featured = Product::featured()->with($with)->limit(8)->get();
        $products = $this->productService->listForHomepage(request());
        $categories = Category::active()->orderBy('sort_order')->get();
        $features = Feature::active()->orderBy('sort_order')->get();
        $testimonials = Testimonial::active()->orderBy('sort_order')->limit(3)->get();

        return view('home', compact('highlights', 'trending', 'featured', 'products', 'categories', 'features', 'testimonials'));
    }
}
