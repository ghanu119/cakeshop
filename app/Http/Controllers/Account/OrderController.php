<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        $customer = $request->user();
        $orders = $this->customerService->ordersForCustomerAccount($customer);

        return view('account.orders.index', compact('customer', 'orders'));
    }

    public function show(Request $request, Order $order): View
    {
        $customer = $request->user();
        abort_unless($order->user_id === $customer->id, 404);

        $order->load(['product' => fn ($q) => $q->withTrashed(), 'product.media', 'media', 'payments']);

        return view('account.orders.show', compact('customer', 'order'));
    }
}
