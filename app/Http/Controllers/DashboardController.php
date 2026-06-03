<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $header = __('Dashboard');
        $ordersCount = auth()->user()->hasRole('Admin') ? Order::count() : null;
        $productsCount = auth()->user()->can('products.view') ? Product::count() : null;
        $categoriesCount = auth()->user()->can('categories.view') ? Category::count() : null;
        $recentEnquiries = auth()->user()->can('contact_enquiries.view')
            ? ContactEnquiry::orderByDesc('created_at')->limit(5)->get()
            : null;

        return view('dashboard', compact('header', 'ordersCount', 'productsCount', 'categoriesCount', 'recentEnquiries'));
    }
}
