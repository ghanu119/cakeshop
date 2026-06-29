<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderNotificationService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderNotificationService $orderNotificationService
    ) {}

    public function index(): View|RedirectResponse
    {
        if (request()->user()->hasRole('Admin')) {
            return redirect()->route('admin.orders.index', ['view' => 'today']);
        }

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
    public function show(Order $order): View|RedirectResponse
    {
        if (request()->user()->hasRole('Admin')) {
            return redirect()->route('admin.orders.show', [
                'order' => $order,
                'view' => 'today',
            ]);
        }

        $order = Order::query()
            ->with(['product.media'])
            ->where('id', $order->id)
            ->kitchenTodayVisible()
            ->firstOrFail();

        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('kitchen.orders.show', [
            'order' => $order,
            'preparationRules' => $preparationRules,
            'readOnly' => false,
            'statusReadOnly' => ! $order->canKitchenUpdateStatus(),
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
            ->kitchenTodayVisible()
            ->firstOrFail();

        $this->authorize('update', $order);

        if (! $order->canKitchenUpdateStatus()) {
            return redirect()->route('admin.kitchen.orders.show', $order)
                ->withErrors(['_form' => __('An administrator must set the order to Processing with a preparation time before you can update the status.')]);
        }

        if (! $order->isPaymentVerified()) {
            return redirect()->route('admin.kitchen.orders.show', $order)
                ->withErrors(['_form' => __('Payment must be verified before you can change the order status.')]);
        }

        $newStatus = $request->validated('order_status');
        $previousStatus = $order->order_status;

        $this->orderService->updateOrderStatus(
            $order,
            $newStatus,
            $request->user()->hasRole('Admin') ? $request->validated('preparation_at') : null
        );

        $order->refresh();

        $this->orderNotificationService->notifyStatusUpdated($order, $previousStatus);

        $stillOnKitchenQueue = Order::query()
            ->whereKey($order->id)
            ->kitchenTodayVisible()
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
