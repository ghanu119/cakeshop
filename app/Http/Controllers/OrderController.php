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
use App\Services\Payments\CheckoutPaymentService;
use App\Services\Payments\PaymentSettingsResolver;
use App\Services\ProductVariantService;
use App\Services\ServiceablePincodeService;
use App\Support\PhoneNormalizer;
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
        private PaymentSettingsResolver $paymentSettingsResolver,
        private CheckoutPaymentService $checkoutPaymentService,
        private ServiceablePincodeService $serviceablePincodeService,
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

        $paymentCheckoutConfig = $this->paymentSettingsResolver->frontendConfig();
        $checkoutPaymentConfig = [
            'pay_before_order' => $this->checkoutPaymentService->shouldUsePayBeforeOrder(),
            'prepare_url' => route('order.checkout.prepare', $product),
            'finalize_url' => route('order.checkout.finalize'),
            'finalize_free_url' => route('order.checkout.finalize-free'),
            'enabled' => (bool) ($paymentCheckoutConfig['enabled'] ?? false),
            'key_id' => $paymentCheckoutConfig['key_id'] ?? null,
        ];

        $isImpersonating = $this->customerContext->isImpersonating();
        $servicablePincodeOptions = $this->serviceablePincodeService->activePincodeMap();

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
            'checkoutPaymentConfig',
            'isImpersonating',
            'servicablePincodeOptions',
        ));
    }

    public function sendCheckoutOtp(Request $request): JsonResponse
    {
        if ($this->customerContext->effectiveCustomer() !== null) {
            abort(403);
        }

        if ($this->isWhatsAppChannel($request)) {
            $validated = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);

            $email = isset($validated['email']) ? strtolower(trim((string) $validated['email'])) : '';

            try {
                $this->customerAuthService->sendWhatsAppOtp($validated['phone']);
            } catch (ValidationException $exception) {
                $response = [
                    'channel' => 'whatsapp',
                    'message' => $email !== ''
                        ? (collect($exception->errors())->flatten()->first()
                            ?? __('We couldn\'t send your WhatsApp code. Please verify by email instead.'))
                        : __('We couldn\'t send a verification code to this number. It may not be on WhatsApp — please check the number or try another one.'),
                ];

                if ($email !== '') {
                    $response['fallback'] = 'email';
                }

                return response()->json($response, 422);
            }

            return response()->json([
                'channel' => 'whatsapp',
                'message' => __('We sent a verification code to your WhatsApp.'),
            ]);
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
            'channel' => 'email',
            'message' => __('We sent a verification code to your email.'),
        ]);
    }

    public function verifyCheckoutOtp(Request $request): JsonResponse
    {
        if ($this->customerContext->effectiveCustomer() !== null) {
            abort(403);
        }

        if ($this->isWhatsAppChannel($request)) {
            $validated = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'code' => ['required', 'string', 'size:6'],
                'guest_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);

            try {
                $this->customerAuthService->verifyWhatsAppOtp($validated['phone'], $validated['code']);
                $this->customerAuthService->authenticateCustomerForVerifiedPhone(
                    $validated['phone'],
                    $validated['guest_name'],
                    $validated['email'] ?? null,
                );
            } catch (ValidationException $exception) {
                return response()->json([
                    'message' => collect($exception->errors())->flatten()->first()
                        ?? __('The code you entered is incorrect.'),
                ], 422);
            }

            return response()->json([
                'verified' => true,
                'authenticated' => true,
                'csrf_token' => csrf_token(),
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
        ]);

        $email = strtolower(trim($validated['email']));

        try {
            $this->customerAuthService->verifyOtp($email, $validated['code']);
            $this->customerAuthService->authenticateCustomerForVerifiedEmail(
                $email,
                $validated['guest_phone'],
                $validated['guest_name'],
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first()
                    ?? __('The code you entered is incorrect.'),
            ], 422);
        }

        return response()->json([
            'verified' => true,
            'authenticated' => true,
            'csrf_token' => csrf_token(),
        ]);
    }

    private function isWhatsAppChannel(Request $request): bool
    {
        return $request->input('channel') === 'whatsapp' && $this->customerAuthService->whatsappEnabled();
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
            'fulfillment_type' => ['nullable', 'string', 'in:takeaway,delivery'],
        ]);

        $customer = $this->customerContext->customerForCheckout($validated['guest_email'] ?? null);
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));

        $unitPrice = (float) $product->price;
        $variant = null;
        if (! empty($validated['product_variant_id']) && $this->productVariantService->hasVariants($product)) {
            try {
                $variant = $this->productVariantService->findVariantForProduct($product, (int) $validated['product_variant_id']);
                $unitPrice = (float) $variant->price;
            } catch (\Throwable) {
                //
            }
        }

        $deliveryCharge = 0.0;
        if (($validated['fulfillment_type'] ?? 'takeaway') === 'delivery') {
            $weightCharge = $variant !== null
                ? $this->productVariantService->weightValueForVariant($variant)?->delivery_charge
                : $product->delivery_charge;
            $deliveryCharge = $weightCharge !== null ? (float) $weightCharge : (float) (settings('default_delivery_charge') ?? 0);
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
                'delivery_charge' => $deliveryCharge,
                'total' => $subtotal + $deliveryCharge,
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
            'delivery_charge' => $deliveryCharge,
            'total' => max(0, $subtotal - ($result['discount_amount'] ?? 0)) + $deliveryCharge,
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

        if ($this->checkoutPaymentService->shouldUsePayBeforeOrder()) {
            return back()->withErrors([
                'checkout' => __('Please complete payment on the checkout page to place your order.'),
            ])->withInput();
        }

        $validated = $request->validated();
        $customer = $this->customerContext->effectiveCustomer();

        $contact = [
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'],
        ];

        if ($customer === null) {
            $verifiedPhone = $this->customerAuthService->verifiedPhone();
            $normalizedPhone = PhoneNormalizer::normalize((string) $contact['guest_phone']);

            if ($verifiedPhone !== null && $normalizedPhone !== null && $verifiedPhone === $normalizedPhone) {
                $this->customerAuthService->assertPhoneOtpVerifiedFor((string) $contact['guest_phone']);
                $customer = $this->customerAuthService->resolveCustomerForVerifiedPhone(
                    (string) $contact['guest_phone'],
                    (string) $contact['guest_name'],
                    ($contact['guest_email'] ?? null) !== null && $contact['guest_email'] !== '' ? (string) $contact['guest_email'] : null,
                );
            } else {
                $this->customerAuthService->assertOtpVerifiedFor((string) $contact['guest_email']);
                $customer = $this->customerAuthService->resolveCustomerForVerifiedEmail(
                    (string) $contact['guest_email'],
                    (string) $contact['guest_phone'],
                    (string) $contact['guest_name'],
                );
            }

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
        $order->load(['product' => fn ($q) => $q->withTrashed(), 'product.media', 'media', 'payments']);

        $paymentCheckoutConfig = active_theme() === 'better-buns'
            ? $this->paymentSettingsResolver->frontendConfig()
            : ['enabled' => false, 'gateway' => null, 'key_id' => null];

        return view('order.confirm', compact('order', 'paymentCheckoutConfig'));
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
