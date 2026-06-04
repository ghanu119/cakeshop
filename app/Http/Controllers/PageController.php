<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function ingredients(): View
    {
        if (active_theme() !== 'better-buns') {
            abort(404);
        }

        $productsWithIngredients = Product::query()
            ->active()
            ->whereNotNull('ingredients')
            ->where('ingredients', '!=', '')
            ->with('category')
            ->orderBy('name_en')
            ->get();

        return view('pages.ingredients', compact('productsWithIngredients'));
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function cookiePolicy(): View
    {
        return view('pages.cookie-policy');
    }
}
