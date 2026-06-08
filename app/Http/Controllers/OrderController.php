<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\OrderService;
use App\Services\ProductVariantService;
use App\Services\OrderNotificationService;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\SubmitPaymentDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ProductVariantService $productVariantService,
        private OrderNotificationService $orderNotificationService
    ) {}

    public function placeForm(Product $product): View
    {
        if (! $product->isActive()) {
            abort(404);
        }
        $deliveryRules = $this->orderService->deliveryAtRules();
        $this->productVariantService->eagerLoadForStorefront($product);
        $product->load([
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ]);
        $variantChoices = $this->productVariantService->choicesForProduct($product);
        $defaultVariant = $this->productVariantService->defaultVariant($product);
        $hasVariants = $this->productVariantService->hasVariants($product);
        $hasFlavors = $product->hasFlavors();

        $messageOnCakeMaxLength = $product->messageOnCakeMaxLength();

        return view('order.place', compact(
            'product',
            'deliveryRules',
            'variantChoices',
            'defaultVariant',
            'hasVariants',
            'hasFlavors',
            'messageOnCakeMaxLength'
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
        if (! empty($validated['flavor_id'])) {
            $duplicateQuery->where('flavor_id', $validated['flavor_id']);
        }
        $recentDuplicate = $duplicateQuery->first();
        if ($recentDuplicate) {
            return redirect()->route('order.confirm', $recentDuplicate)
                ->with('status', __('Your order was already received. You can view or submit payment details below.'));
        }

        $order = $this->orderService->createOrder($product, $validated);

        $this->orderNotificationService->notifyOrderPlaced($order);

        return redirect()->route('order.confirm', $order)
            ->with('order_placed', true);
    }

    public function confirm(Order $order): View|RedirectResponse
    {
        $order->load(['product' => fn ($q) => $q->withTrashed(), 'product.media', 'media']);

        return view('order.confirm', compact('order'));
    }

    public function downloadPaymentQr(): BinaryFileResponse
    {
        $media = SiteSetting::first()?->getFirstMedia('payment_qr');

        if ($media === null || ! is_file($media->getPath())) {
            abort(404);
        }

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'jpg';

        return response()->download(
            $media->getPath(),
            'payment-qr.'.$extension,
            ['Content-Type' => $media->mime_type ?? 'image/jpeg']
        );
    }

    public function submitPaymentForm(?Order $order = null): View|RedirectResponse
    {
        if ($order === null) {
            return view('order.submit-payment-lookup');
        }
        $order->load('media');

        return view('order.submit-payment', compact('order'));
    }

    public function submitPaymentLookup(Request $request): RedirectResponse
    {
        $request->validate([
            'order_no' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);
        $order = Order::where('order_no', $request->input('order_no'))->first();
        if (! $order || $order->guest_phone !== $request->input('phone')) {
            return back()->withErrors(['phone' => __('The order reference or phone number does not match our records.')])->withInput();
        }

        return redirect()->route('order.submit-payment', $order);
    }

    public function submitPayment(SubmitPaymentDetailsRequest $request, Order $order): RedirectResponse
    {
        $isUpdate = $order->hasPaymentDetailsSubmitted();
        $data = $request->validated();
        $this->orderService->submitPaymentDetails($order, $data);

        if ($request->hasFile('payment_proof')) {
            $order->addMediaFromRequest('payment_proof')
                ->toMediaCollection('payment_proof');
        }

        $this->orderNotificationService->notifyPaymentSubmitted($order, $isUpdate);

        $statusMessage = $isUpdate
            ? __('Payment details updated. We will verify and update your order shortly.')
            : __('Payment details submitted. We will verify and update your order shortly.');

        return redirect()->route('order.confirm', $order)
            ->with('status', $statusMessage);
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
