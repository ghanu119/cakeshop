<?php

namespace App\Http\Controllers\Admin;

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
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->listForAdmin(request());

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['product', 'media']);
        $preparationRules = $this->orderService->preparationAtRules($order);

        return view('admin.orders.show', compact('order', 'preparationRules'));
    }

    public function verifyPayment(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->verifyPayment($order);

        return redirect()->route('admin.orders.show', $order)->with('status', __('Payment verified.'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        if (! $order->isPaymentVerified()) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('Payment must be verified before you can change the order status.'));
        }

        $this->orderService->updateOrderStatus(
            $order,
            $request->validated('order_status'),
            $request->validated('preparation_at')
        );

        if ($order->guest_email) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->guest_email)->send(
                    new \App\Mail\OrderStatusUpdated($order)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $redirect = request()->query('from') === 'kitchen'
            ? route('admin.kitchen.orders.index')
            : route('admin.orders.show', $order);

        return redirect()->to($redirect)->with('status', __('Order status updated.'));
    }
}
