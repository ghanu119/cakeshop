<?php

namespace App\Http\Controllers\Admin;

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

    public function index(): View
    {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->listForAdmin(request());

        $isTodayEntry = request('view') === 'today';
        $showTodayChrome = request()->boolean('delivery_today') || $isTodayEntry;
        $todayListMode = $isTodayEntry || (request()->ajax() && request()->boolean('delivery_today'));

        if (request()->ajax()) {
            return view('admin.orders.partials._list-results', compact('orders', 'showTodayChrome', 'todayListMode'));
        }

        return view('admin.orders.index', compact('orders', 'showTodayChrome', 'isTodayEntry', 'todayListMode'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['product.media', 'media', 'placedBy']);
        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('admin.orders.show', compact('order', 'preparationRules'));
    }

    public function verifyPayment(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->verifyPayment($order);

        $this->orderNotificationService->notifyPaymentVerified($order->fresh());

        $showParams = ['order' => $order];
        if (request()->boolean('delivery_today') || request('view') === 'today') {
            $showParams['view'] = 'today';
        }

        return redirect()->route('admin.orders.show', $showParams)->with('status', __('Payment verified.'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        if (! $order->isPaymentVerified()) {
            $showParams = ['order' => $order];
            if (request()->boolean('delivery_today') || request('view') === 'today') {
                $showParams['view'] = 'today';
            }

            return redirect()->route('admin.orders.show', $showParams)
                ->withErrors(['_form' => __('Payment must be verified before you can change the order status.')]);
        }

        $previousStatus = $order->order_status;

        $this->orderService->updateOrderStatus(
            $order,
            $request->validated('order_status'),
            $request->validated('preparation_at')
        );

        $order->refresh();

        $this->orderNotificationService->notifyStatusUpdated($order, $previousStatus);

        if (Order::query()->whereKey($order->id)->kitchenTodayQueue()->exists()) {
            $this->orderNotificationService->notifyKitchenOrderQueued($order);
        }

        $todayContext = request()->boolean('delivery_today') || request('view') === 'today' || request()->query('from') === 'kitchen';

        $redirect = $todayContext
            ? route('admin.orders.show', ['order' => $order, 'view' => 'today'])
            : route('admin.orders.show', $order);

        return redirect()->to($redirect)->with('status', __('Order status updated.'));
    }
}
