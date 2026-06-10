<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\Order;
use App\Models\Product;
use App\Services\DashboardService;
use App\Services\OrderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $header = __('Dashboard');
        $ordersCount = null;
        $adminDashboard = null;
        $productsCount = auth()->user()->can('products.view') ? Product::count() : null;
        $categoriesCount = auth()->user()->can('categories.view') ? Category::count() : null;
        $recentEnquiries = auth()->user()->can('contact_enquiries.view')
            ? ContactEnquiry::orderByDesc('created_at')->limit(5)->get()
            : null;

        $todayOrders = null;
        $upcomingOrders = null;
        $upcomingTotal = null;

        if (auth()->user()->hasRole('Admin') && auth()->user()->can('orders.view')) {
            $adminDashboard = $this->dashboardService->dataForAdmin();
        } elseif (auth()->user()->hasRole('Admin')) {
            $ordersCount = Order::count();
        }

        if (auth()->user()->hasRole('Kitchen') && auth()->user()->can('orders.view')) {
            $todayOrders = $this->orderService->listKitchenTodayForDashboard();
            $upcomingOrders = $this->orderService->listKitchenUpcomingPreview();
            $upcomingTotal = $this->orderService->countKitchenUpcoming();
        }

        if (request()->routeIs('admin.dashboard') && auth()->user()->unreadNotifications()->exists()) {
            session()->put('notifications_catchup_shown', true);
        }

        return view('dashboard', compact(
            'header',
            'ordersCount',
            'adminDashboard',
            'productsCount',
            'categoriesCount',
            'recentEnquiries',
            'todayOrders',
            'upcomingOrders',
            'upcomingTotal',
        ));
    }
}
