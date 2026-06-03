<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();
        $products = Product::where('category_id', $category->id)->active()->with([
            'media',
            'variants' => fn ($q) => $q->active()->orderBy('sort_order'),
            'variants.selections.value',
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ])->orderBy('name_en')->paginate(12);

        return view('categories.show', compact('category', 'products'));
    }
}
