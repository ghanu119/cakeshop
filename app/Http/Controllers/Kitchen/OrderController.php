<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(): View
    {
        $orders = $this->orderService->listForKitchen();

        return view('kitchen.orders.index', compact('orders'));
    }

    /**
     * Show order details (no payment info) for a today's order. Only orders visible to kitchen are allowed.
     */
    public function show(Order $order): View
    {
        $order = Order::query()
            ->with(['product'])
            ->where('id', $order->id)
            ->visibleToKitchen()
            ->firstOrFail();

        return view('kitchen.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order = Order::query()
            ->where('id', $order->id)
            ->visibleToKitchen()
            ->firstOrFail();

        $this->authorize('update', $order);

        if (! $order->isPaymentVerified()) {
            return redirect()->route('admin.kitchen.orders.show', $order)
                ->with('error', __('Payment must be verified before you can change the order status.'));
        }

        $this->orderService->updateOrderStatus($order, $request->validated('order_status'));

        return redirect()->route('admin.kitchen.orders.show', $order)->with('status', __('Order status updated.'));
    }
}
