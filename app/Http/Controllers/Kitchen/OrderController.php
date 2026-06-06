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

    public function upcomingIndex(): View
    {
        $orders = $this->orderService->listKitchenUpcoming();

        return view('kitchen.orders.upcoming', compact('orders'));
    }

    /**
     * Show order details (no payment info) for a today's order. Only orders visible to kitchen are allowed.
     */
    public function show(Order $order): View
    {
        $order = Order::query()
            ->with(['product.media'])
            ->where('id', $order->id)
            ->kitchenTodayQueue()
            ->firstOrFail();

        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('kitchen.orders.show', [
            'order' => $order,
            'preparationRules' => $preparationRules,
            'readOnly' => false,
        ]);
    }

    public function upcomingShow(Order $order): View
    {
        $order = Order::query()
            ->with(['product.media'])
            ->where('id', $order->id)
            ->kitchenUpcoming()
            ->firstOrFail();

        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('kitchen.orders.show', [
            'order' => $order,
            'preparationRules' => $preparationRules,
            'readOnly' => true,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order = Order::query()
            ->where('id', $order->id)
            ->kitchenTodayQueue()
            ->firstOrFail();

        $this->authorize('update', $order);

        if (! $order->canKitchenUpdateStatus()) {
            return redirect()->route('admin.kitchen.orders.show', $order)
                ->with('error', __('You can only update the status of today\'s processing orders.'));
        }

        if (! $order->isPaymentVerified()) {
            return redirect()->route('admin.kitchen.orders.show', $order)
                ->with('error', __('Payment must be verified before you can change the order status.'));
        }

        $newStatus = $request->validated('order_status');

        $this->orderService->updateOrderStatus(
            $order,
            $newStatus,
            $request->user()->hasRole('Admin') ? $request->validated('preparation_at') : null
        );

        $order->refresh();

        $stillOnKitchenQueue = Order::query()
            ->whereKey($order->id)
            ->kitchenTodayQueue()
            ->exists();

        if (! $stillOnKitchenQueue) {
            $message = match ($newStatus) {
                'completed' => __('Order marked as completed and removed from today\'s kitchen list.'),
                'cancelled' => __('Order cancelled and removed from today\'s kitchen list.'),
                default => __('Order status updated. It is no longer on today\'s kitchen list.'),
            };

            return redirect()
                ->route('admin.kitchen.orders.index')
                ->with('status', $message);
        }

        return redirect()
            ->route('admin.kitchen.orders.show', $order)
            ->with('status', __('Order status updated.'));
    }
}
