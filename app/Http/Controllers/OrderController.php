<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\SubmitPaymentDetailsRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\CouponService;
use App\Services\CustomerAuthService;
use App\Services\CustomerContext;
use App\Services\OrderNotificationService;
use App\Services\OrderService;
use App\Services\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ProductVariantService $productVariantService,
        private OrderNotificationService $orderNotificationService,
        private CustomerContext $customerContext,
        private CustomerAuthService $customerAuthService,
        private CouponService $couponService,
    ) {}

    public function placeForm(Product $product): View
    {
        if (! $product->isActive()) {
            abort(404);
        }
        $customer = $this->customerContext->effectiveCustomer();
        $deliveryRules = $this->orderService->deliveryAtRules($product);
        $suggestedDeliveryAt = $this->orderService->suggestedDeliveryAt($deliveryRules);
        $deliveryBounds = $this->orderService->deliveryAtBoundsForInput($deliveryRules);
        $this->productVariantService->eagerLoadForStorefront($product);
        $product->load([
            'flavors' => fn ($q) => $q->active()->orderByPivot('sort_order'),
        ]);
        $variantChoices = $this->productVariantService->choicesForProduct($product);
        $defaultVariant = $this->productVariantService->defaultVariant($product);
        $hasVariants = $this->productVariantService->hasVariants($product);
        $hasFlavors = $product->hasFlavors();

        $messageOnCakeMaxLength = $product->messageOnCakeMaxLength();

        $defaultQuantity = max(1, (int) old('quantity', request('quantity', 1)));
        $unitPrice = (float) ($defaultVariant?->price ?? $product->price);
        $initialVariantId = (int) old('product_variant_id', request('product_variant_id', $defaultVariant?->id ?? 0));

        if ($initialVariantId > 0 && $hasVariants) {
            try {
                $initialVariant = $this->productVariantService->findVariantForProduct($product, $initialVariantId);
                $unitPrice = (float) $initialVariant->price;
            } catch (\Throwable) {
                //
            }
        }

        $defaultSubtotal = $unitPrice * $defaultQuantity;

        $universalCoupons = $this->couponService->listUniversalCouponsForCheckout(
            $product,
            $customer,
            $defaultSubtotal
        );

        $autoApplyPreview = null;
        if ($universalCoupons->count() === 1 && $universalCoupons->first()['auto_apply']) {
            $autoApplyPreview = $universalCoupons->first();
        }

        $defaultCouponId = $this->couponService->defaultCheckoutCouponId($universalCoupons);

        $defaultCoupon = $defaultCouponId
            ? $universalCoupons->firstWhere('id', $defaultCouponId)
            : null;
        $defaultCouponCode = $defaultCoupon['code'] ?? null;

        return view('order.place', compact(
            'product',
            'customer',
            'deliveryRules',
            'deliveryBounds',
            'suggestedDeliveryAt',
            'variantChoices',
            'defaultVariant',
            'hasVariants',
            'hasFlavors',
            'messageOnCakeMaxLength',
            'universalCoupons',
            'autoApplyPreview',
            'defaultCouponId',
            'defaultCouponCode',
        ));
    }

    public function sendCheckoutOtp(Request $request): JsonResponse
    {
        if ($this->customerContext->effectiveCustomer() !== null) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $existingUser = User::where('email', $email)->first();
        if ($existingUser?->isStaff()) {
            throw ValidationException::withMessages([
                'email' => [__('This email cannot be used for customer checkout.')],
            ]);
        }

        $this->customerAuthService->sendOtp($email);

        return response()->json([
            'message' => __('We sent a verification code to your email.'),
        ]);
    }

    public function verifyCheckoutOtp(Request $request): JsonResponse
    {
        if ($this->customerContext->effectiveCustomer() !== null) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($validated['email']));

        try {
            $this->customerAuthService->verifyOtp($email, $validated['code']);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first()
                    ?? __('The code you entered is incorrect.'),
            ], 422);
        }

        return response()->json(['verified' => true]);
    }

    public function validateCoupon(Request $request, Product $product): JsonResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'product_variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'guest_email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = $this->customerContext->customerForCheckout($validated['guest_email'] ?? null);
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));

        $unitPrice = (float) $product->price;
        if (! empty($validated['product_variant_id']) && $this->productVariantService->hasVariants($product)) {
            try {
                $variant = $this->productVariantService->findVariantForProduct($product, (int) $validated['product_variant_id']);
                $unitPrice = (float) $variant->price;
            } catch (\Throwable) {
                //
            }
        }

        $subtotal = $unitPrice * $quantity;
        $manualCode = $validated['coupon_code'] ?? null;
        $couponId = isset($validated['coupon_id']) ? (int) $validated['coupon_id'] : null;
        $autoSelectBest = $request->boolean('auto_select_best');
        $skipAutoApply = $request->boolean('skip_auto_apply');

        if ($manualCode !== null && trim($manualCode) !== '') {
            $couponId = null;
        }

        $universalCoupons = $this->couponService->listUniversalCouponsForCheckout(
            $product,
            $customer,
            $subtotal,
        );
        $bestCouponId = $this->couponService->defaultCheckoutCouponId($universalCoupons);
        $bestCoupon = $bestCouponId
            ? $universalCoupons->firstWhere('id', $bestCouponId)
            : null;
        $bestCouponCode = $bestCoupon['code'] ?? null;

        if ($skipAutoApply && ($manualCode === null || trim((string) $manualCode) === '')) {
            return response()->json([
                'valid' => false,
                'discount_amount' => 0,
                'label' => null,
                'message' => null,
                'max_cap' => null,
                'coupon_code' => null,
                'reason' => null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'best_coupon_id' => $bestCouponId,
                'best_coupon_code' => $bestCouponCode,
                'universal_coupons' => $universalCoupons->values()->all(),
            ]);
        }

        if ($autoSelectBest && ($manualCode === null || trim((string) $manualCode) === '')) {
            $couponId = $bestCouponId;
        }

        $result = $this->couponService->validateForPreview(
            $product,
            $customer,
            $subtotal,
            $manualCode,
            $couponId
        );

        return response()->json(array_merge($result, [
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - ($result['discount_amount'] ?? 0)),
            'best_coupon_id' => $bestCouponId,
            'best_coupon_code' => $bestCouponCode,
            'universal_coupons' => $universalCoupons->values()->all(),
        ]));
    }

    public function place(PlaceOrderRequest $request, Product $product): RedirectResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $validated = $request->validated();
        $customer = $this->customerContext->effectiveCustomer();

        $contact = [
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'],
        ];

        if ($customer === null) {
            $this->customerAuthService->assertOtpVerifiedFor((string) $contact['guest_email']);
            $customer = $this->customerAuthService->resolveCustomerForVerifiedEmail(
                (string) $contact['guest_email'],
                (string) $contact['guest_phone'],
                (string) $contact['guest_name'],
            );
            $this->customerAuthService->loginCustomer($customer);
        }

        $validated = array_merge($validated, $contact);

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

        if ($this->customerContext->isImpersonating()) {
            $impersonator = $this->customerContext->impersonator();
            $customer = $this->customerContext->effectiveCustomer();
            if ($impersonator && $customer) {
                $this->customerContext->logOrderPlaced($impersonator, $customer, $order->id);
            }
        }

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
        $order = $this->findOrderByReferenceAndPhone(
            $request->input('order_no'),
            $request->input('phone')
        );
        if ($order === null) {
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
            'order_no' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);
        $order = $this->findOrderByReferenceAndPhone(
            $request->input('order_no'),
            $request->input('phone')
        );
        if ($order === null) {
            return back()->withErrors(['phone' => __('The order reference or phone number does not match our records.')])->withInput();
        }

        return view('order.history', [
            'order' => $order,
            'phone' => $request->input('phone'),
            'order_no' => $request->input('order_no'),
        ]);
    }

    private function findOrderByReferenceAndPhone(string $orderNo, string $phone): ?Order
    {
        $order = Order::query()
            ->with(['product' => fn ($q) => $q->withTrashed()])
            ->where('order_no', $orderNo)
            ->first();

        if ($order === null || $order->guest_phone !== $phone) {
            return null;
        }

        return $order;
    }
}
