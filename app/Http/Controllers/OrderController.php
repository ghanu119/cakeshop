<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\ProductVariantService;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\SubmitPaymentDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ProductVariantService $productVariantService
    ) {}

    public function placeForm(Product $product): View
    {
        if (! $product->isActive()) {
            abort(404);
        }
        $deliveryRules = $this->orderService->deliveryAtRules();
        $this->productVariantService->eagerLoadForStorefront($product);
        $variantChoices = $this->productVariantService->choicesForProduct($product);
        $defaultVariant = $this->productVariantService->defaultVariant($product);
        $hasVariants = $this->productVariantService->hasVariants($product);

        return view('order.place', compact(
            'product',
            'deliveryRules',
            'variantChoices',
            'defaultVariant',
            'hasVariants'
        ));
    }

    public function place(PlaceOrderRequest $request, Product $product): RedirectResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $validated = $request->validated();
        $duplicateQuery = Order::query()
            ->where('product_id', $product->id)
            ->where('guest_phone', $validated['guest_phone'])
            ->where('quantity', (int) ($validated['quantity'] ?? 1))
            ->where('ordered_at', '>=', now()->subSeconds(90));
        if (! empty($validated['product_variant_id'])) {
            $duplicateQuery->where('product_variant_id', $validated['product_variant_id']);
        }
        $recentDuplicate = $duplicateQuery->first();
        if ($recentDuplicate) {
            return redirect()->route('order.confirm', ['uuid' => $recentDuplicate->uuid])
                ->with('status', __('Your order was already received. You can view or submit payment details below.'));
        }

        $order = $this->orderService->createOrder($product, $validated);

        $adminEmail = settings('admin_email');
        if ($adminEmail) {
            try {
                $order->load('product');
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\NewOrderNotification($order));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('order.confirm', ['uuid' => $order->uuid]);
    }

    public function confirm(string $uuid): View|RedirectResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $order->load(['product' => fn ($q) => $q->withTrashed()]);

        return view('order.confirm', compact('order'));
    }

    public function submitPaymentForm(?string $uuid = null): View|RedirectResponse
    {
        if ($uuid === null) {
            return view('order.submit-payment-lookup');
        }
        $order = Order::where('uuid', $uuid)->firstOrFail();

        return view('order.submit-payment', compact('order'));
    }

    public function submitPaymentLookup(Request $request): RedirectResponse
    {
        $request->validate([
            'uuid' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);
        $order = Order::where('uuid', $request->input('uuid'))->first();
        if (! $order || $order->guest_phone !== $request->input('phone')) {
            return back()->withErrors(['phone' => __('The order reference or phone number does not match our records.')])->withInput();
        }

        return redirect()->route('order.submit-payment', ['uuid' => $order->uuid]);
    }

    public function submitPayment(SubmitPaymentDetailsRequest $request, string $uuid): RedirectResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $data = $request->validated();
        $this->orderService->submitPaymentDetails($order, $data);

        if ($request->hasFile('payment_proof')) {
            $order->addMediaFromRequest('payment_proof')
                ->toMediaCollection('payment_proof');
        }

        return redirect()->route('order.confirm', ['uuid' => $order->uuid])
            ->with('status', __('Payment details submitted. We will verify and update your order shortly.'));
    }

    public function historyForm(): View
    {
        return view('order.history');
    }

    public function historySearch(Request $request): View|RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:6'],
        ]);
        $phone = $request->input('phone');
        $normalized = preg_replace('/\D/', '', $phone);
        if (strlen($normalized) < 6) {
            return back()->withErrors(['phone' => __('Please enter at least 6 digits.')])->withInput();
        }
        $orders = Order::query()
            ->with(['product' => fn ($q) => $q->withTrashed()])
            ->whereRaw('REPLACE(REPLACE(REPLACE(guest_phone, " ", ""), "-", ""), "+", "") LIKE ?', ['%' . $normalized . '%'])
            ->orderByDesc('ordered_at')
            ->limit(50)
            ->get();

        return view('order.history', ['orders' => $orders, 'phone' => $phone]);
    }
}
