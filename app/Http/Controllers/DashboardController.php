<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): View
    {
        $header = __('Dashboard');
        $ordersCount = auth()->user()->hasRole('Admin') ? Order::count() : null;
        $productsCount = auth()->user()->can('products.view') ? Product::count() : null;
        $categoriesCount = auth()->user()->can('categories.view') ? Category::count() : null;
        $recentEnquiries = auth()->user()->can('contact_enquiries.view')
            ? ContactEnquiry::orderByDesc('created_at')->limit(5)->get()
            : null;

        $todayOrders = null;
        $upcomingOrders = null;
        $upcomingTotal = null;

        if (auth()->user()->hasRole('Kitchen') && auth()->user()->can('orders.view')) {
            $todayOrders = $this->orderService->listKitchenTodayForDashboard();
            $upcomingOrders = $this->orderService->listKitchenUpcomingPreview();
            $upcomingTotal = $this->orderService->countKitchenUpcoming();
        }

        return view('dashboard', compact(
            'header',
            'ordersCount',
            'productsCount',
            'categoriesCount',
            'recentEnquiries',
            'todayOrders',
            'upcomingOrders',
            'upcomingTotal',
        ));
    }
}
