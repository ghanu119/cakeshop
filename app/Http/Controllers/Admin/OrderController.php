<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordInStoreCashPaymentRequest;
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
        $paymentStats = $this->orderService->paymentStatsForAdminList(request());

        $isTodayEntry = request('view') === 'today';
        $showTodayChrome = request()->boolean('delivery_today') || $isTodayEntry;
        $todayListMode = $isTodayEntry || (request()->ajax() && request()->boolean('delivery_today'));

        if (request()->ajax()) {
            return view('admin.orders.partials._list-results', compact('orders', 'showTodayChrome', 'todayListMode', 'paymentStats'));
        }

        return view('admin.orders.index', compact('orders', 'showTodayChrome', 'isTodayEntry', 'todayListMode', 'paymentStats'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['product.media', 'media', 'placedBy', 'payments']);
        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('admin.orders.show', compact('order', 'preparationRules'));
    }

    public function verifyPayment(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->isPaymentVerified()) {
            return redirect()->route('admin.orders.show', $this->showRedirectParams($order))
                ->withErrors(['_form' => __('Payment is already verified for this order.')]);
        }

        $this->orderService->verifyPayment($order);

        $order->refresh();

        $this->orderNotificationService->notifyPaymentVerified($order);

        $message = $order->isInStoreOrder()
            ? ($order->hasOutstandingBalance()
                ? __('Payment verified for kitchen. ₹:amount is still due on this order.', [
                    'amount' => number_format($order->balanceDue(), 2),
                ])
                : __('Payment marked as fully collected.'))
            : __('Payment verified.');

        return redirect()->route('admin.orders.show', $this->showRedirectParams($order))
            ->with('status', $message);
    }

    public function recordCashPayment(RecordInStoreCashPaymentRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->recordInStoreCashPayment(
            $order,
            (float) $request->validated('amount_received')
        );

        $order->refresh();

        if ($order->isPaymentVerified()) {
            $this->orderNotificationService->notifyPaymentVerified($order);
        }

        return redirect()->route('admin.orders.show', $this->showRedirectParams($order))
            ->with('status', __('Cash payment recorded.'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        if ($order->requiresPaymentBeforeStatusChange() && ! $order->isPaymentVerified()) {
            return redirect()->route('admin.orders.show', $this->showRedirectParams($order))
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

    /**
     * @return array<string, mixed>
     */
    private function showRedirectParams(Order $order): array
    {
        $params = ['order' => $order];

        if (request()->boolean('delivery_today') || request('view') === 'today') {
            $params['view'] = 'today';
        }

        return $params;
    }
}
