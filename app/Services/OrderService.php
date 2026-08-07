<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Payments\DTOs\VerifyPaymentResult;
use App\Services\Payments\PaymentOrchestrator;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const ADMIN_SORTABLE_COLUMNS = [
        'order_no',
        'guest_name',
        'amount',
        'payment_status',
        'order_status',
        'fulfillment_type',
        'ordered_at',
        'delivery_at',
    ];

    public const KITCHEN_UPCOMING_PREVIEW_LIMIT = 6;

    public const ADMIN_TODAY_DELIVERY_PREVIEW_LIMIT = 8;

    public const ADMIN_UPCOMING_PREVIEW_LIMIT = 6;

    public const ADMIN_PAYMENT_REVIEW_PREVIEW_LIMIT = 5;

    public const ADMIN_IN_KITCHEN_PREVIEW_LIMIT = 4;

    public function __construct(
        private ProductVariantService $productVariantService,
        private ServiceablePincodeService $pincodeService,
        private CouponService $couponService,
        private PaymentOrchestrator $paymentOrchestrator,
    ) {}

    public function listForAdmin(Request $request): LengthAwarePaginator
    {
        $query = $this->buildAdminListQuery($request)->with(['product', 'media']);

        $this->applyAdminListSorting($query, $request);

        return $query->paginate(15)->withQueryString();
    }

    /**
     * @return array{
     *     order_count: int,
     *     total_order_amount: float,
     *     online_received: float,
     *     cash_received: float,
     *     total_received: float,
     *     pending_amount: float,
     *     cash_due: float,
     *     total_remaining: float
     * }
     */
    public function paymentStatsForAdminList(Request $request): array
    {
        $cashOnStore = Order::PAYMENT_METHOD_CASH_ON_STORE;
        $verified = Order::PAYMENT_STATUS_VERIFIED;
        $pending = Order::PAYMENT_STATUS_PENDING;
        $partiallyPaid = Order::PAYMENT_STATUS_PARTIALLY_PAID;

        $stats = $this->buildAdminListQuery($request)
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_order_amount')
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN payment_status = '{$verified}' AND NOT (payment_method = '{$cashOnStore}' OR placed_by_user_id IS NOT NULL) THEN COALESCE(payment_amount, amount) ELSE 0 END), 0) as online_received"
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN payment_method = '{$cashOnStore}' OR placed_by_user_id IS NOT NULL THEN COALESCE(payment_amount, 0) ELSE 0 END), 0) as cash_received"
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN payment_status IN ('{$pending}', '{$partiallyPaid}') THEN amount ELSE 0 END), 0) as pending_amount"
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN (payment_method = '{$cashOnStore}' OR placed_by_user_id IS NOT NULL) AND amount - COALESCE(payment_amount, 0) > 0.01 THEN amount - COALESCE(payment_amount, 0) ELSE 0 END), 0) as cash_due"
            )
            ->first();

        $onlineReceived = round((float) ($stats->online_received ?? 0), 2);
        $cashReceived = round((float) ($stats->cash_received ?? 0), 2);
        $totalOrderAmount = round((float) ($stats->total_order_amount ?? 0), 2);
        $totalReceived = round($onlineReceived + $cashReceived, 2);
        $pendingAmount = round((float) ($stats->pending_amount ?? 0), 2);
        $cashDue = round((float) ($stats->cash_due ?? 0), 2);
        $totalRemaining = round(max(0, $totalOrderAmount - $totalReceived), 2);

        return [
            'order_count' => (int) ($stats->order_count ?? 0),
            'total_order_amount' => $totalOrderAmount,
            'online_received' => $onlineReceived,
            'cash_received' => $cashReceived,
            'total_received' => $totalReceived,
            'pending_amount' => $pendingAmount,
            'cash_due' => $cashDue,
            'total_remaining' => $totalRemaining,
        ];
    }

    public function buildAdminListQuery(Request $request): Builder
    {
        $query = Order::query();

        $this->applyAdminListFilters($query, $request);

        return $query;
    }

    private function applyAdminListFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('guest_phone', 'like', "%{$term}%")
                    ->orWhere('guest_name', 'like', "%{$term}%")
                    ->orWhere('order_no', 'like', "%{$term}%")
                    ->orWhere('flavor_name', 'like', "%{$term}%");
            });
        }
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->input('order_status'));
        }
        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === Order::PAYMENT_METHOD_CASH_ON_STORE) {
                $query->where('payment_method', Order::PAYMENT_METHOD_CASH_ON_STORE);
            } elseif ($request->input('payment_status') === 'in_store_outstanding') {
                $query->inStoreOutstanding();
            } else {
                $query->where('payment_status', $request->input('payment_status'));
            }
        }
        if ($request->filled('from_date')) {
            $query->whereDate('ordered_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('ordered_at', '<=', $request->input('to_date'));
        }
        if ($request->boolean('delivery_today') || $request->query('view') === 'today') {
            $query->deliveryToday();
        }
        if ($request->boolean('awaiting_payment_verification')) {
            $query->awaitingPaymentVerification();
        }
    }

    private function applyAdminListSorting(Builder $query, Request $request): void
    {
        $isTodayView = $request->query('view') === 'today' || $request->boolean('delivery_today');
        $sort = $request->input('sort', $isTodayView ? 'delivery_at' : 'ordered_at');
        $direction = strtolower((string) $request->input('direction', $isTodayView ? 'asc' : 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, self::ADMIN_SORTABLE_COLUMNS, true)) {
            $sort = 'ordered_at';
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction)->orderByDesc('id');
    }

    public function listForKitchen(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['product.media'])
            ->kitchenTodayVisible()
            ->orderByRaw('CASE WHEN preparation_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('preparation_at')
            ->orderBy('delivery_at')
            ->paginate(20);
    }

    public function listKitchenTodayForDashboard(): Collection
    {
        return Order::query()
            ->with(['product.media'])
            ->kitchenTodayVisible()
            ->orderByRaw('CASE WHEN preparation_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('preparation_at')
            ->orderBy('delivery_at')
            ->get();
    }

    public function listKitchenTodayActionableForDashboard(): Collection
    {
        return Order::query()
            ->with(['product.media'])
            ->kitchenTodayQueue()
            ->orderBy('preparation_at')
            ->get();
    }

    public function listKitchenUpcomingPreview(int $limit = self::KITCHEN_UPCOMING_PREVIEW_LIMIT): Collection
    {
        return Order::query()
            ->with(['product.media'])
            ->kitchenUpcoming()
            ->orderBy('delivery_at')
            ->limit($limit)
            ->get();
    }

    public function countKitchenUpcoming(): int
    {
        return Order::query()->kitchenUpcoming()->count();
    }

    public function listKitchenUpcoming(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['product.media'])
            ->kitchenUpcoming()
            ->orderBy('delivery_at')
            ->paginate(20);
    }

    public function listAdminTodayDeliveriesPreview(int $limit = self::ADMIN_TODAY_DELIVERY_PREVIEW_LIMIT): Collection
    {
        return Order::query()
            ->with(['product.media'])
            ->deliveryToday()
            ->orderBy('delivery_at')
            ->limit($limit)
            ->get();
    }

    public function countAdminTodayDeliveries(): int
    {
        return Order::query()->deliveryToday()->count();
    }

    public function listAdminUpcomingPreview(int $limit = self::ADMIN_UPCOMING_PREVIEW_LIMIT): Collection
    {
        return Order::query()
            ->with(['product.media'])
            ->deliveryUpcoming()
            ->orderBy('delivery_at')
            ->limit($limit)
            ->get();
    }

    public function countAdminUpcoming(): int
    {
        return Order::query()->deliveryUpcoming()->count();
    }

    public function listAdminPaymentReviewPreview(int $limit = self::ADMIN_PAYMENT_REVIEW_PREVIEW_LIMIT): Collection
    {
        return Order::query()
            ->with(['product', 'media'])
            ->awaitingPaymentVerification()
            ->orderBy('payment_made_at')
            ->orderBy('ordered_at')
            ->limit($limit)
            ->get();
    }

    public function countAdminPaymentReview(): int
    {
        return Order::query()->awaitingPaymentVerification()->count();
    }

    public function listAdminRecentOrders(int $limit = 5): Collection
    {
        return Order::query()
            ->with(['product'])
            ->orderByDesc('ordered_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{
     *     deliveriesToday: int,
     *     inKitchen: int,
     *     awaitingVerification: int,
     *     revenueToday: float,
     *     ordersThisWeek: int
     * }
     */
    public function adminDashboardStats(): array
    {
        return [
            'deliveriesToday' => Order::query()->deliveryToday()->count(),
            'inKitchen' => Order::query()->kitchenTodayQueue()->count(),
            'awaitingVerification' => Order::query()->awaitingPaymentVerification()->count(),
            'revenueToday' => (float) Order::query()
                ->deliveryToday()
                ->paymentVerified()
                ->sum('amount'),
            'ordersThisWeek' => Order::query()->orderedThisWeek()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{amount: float, subtotal: float, discount_amount: float, delivery_charge: float, currency: string}
     */
    public function quoteOrder(Product $product, array $data, ?User $customer = null): array
    {
        $pricing = $this->resolveOrderPricing($product, $data, $customer);

        return [
            'amount' => $pricing['amount'],
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount_amount'],
            'delivery_charge' => $pricing['delivery_charge'],
            'currency' => (string) (settings('currency') ?: config('payment.currency', 'INR')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOrder(Product $product, array $data): Order
    {
        $customer = app(CustomerContext::class)->effectiveCustomer();
        $pricing = $this->resolveOrderPricing($product, $data, $customer);

        $order = $this->buildOrderFromPricing($product, $data, $customer, $pricing);
        $this->paymentOrchestrator->initializeOrderPayment($order);

        if (app(CustomerContext::class)->isImpersonating()) {
            $this->applyInStoreCashReceived($order, (float) ($data['cash_received'] ?? 0));
        }

        $order->save();

        if ($pricing['variant'] !== null) {
            $this->productVariantService->snapshotOrder($product, $pricing['variant'], $order);
        }

        return $order;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOrderWithVerifiedPayment(Product $product, array $data, VerifyPaymentResult $paymentResult): Order
    {
        $customer = app(CustomerContext::class)->effectiveCustomer();
        $pricing = $this->resolveOrderPricing($product, $data, $customer);

        $order = $this->buildOrderFromPricing($product, $data, $customer, $pricing);
        $order->payment_method = Order::PAYMENT_METHOD_RAZORPAY;
        $order->payment_status = 'verified';
        $order->save();

        if ($pricing['variant'] !== null) {
            $this->productVariantService->snapshotOrder($product, $pricing['variant'], $order);
        }

        app(PaymentService::class)->createPaidPayment($order, $paymentResult);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     amount: float,
     *     subtotal: float,
     *     discount_amount: float,
     *     delivery_charge: float,
     *     unit_price: float,
     *     variant: \App\Models\ProductVariant|null,
     *     coupon_result: array{coupon: \App\Models\Coupon, discount_amount: float, label: string}|null
     * }
     */
    private function resolveOrderPricing(Product $product, array $data, ?User $customer): array
    {
        $quantity = (int) ($data['quantity'] ?? 1);
        $variant = null;
        $unitPrice = (float) $product->price;

        if ($this->productVariantService->hasVariants($product)) {
            $variant = $this->productVariantService->findVariantForProduct(
                $product,
                (int) $data['product_variant_id']
            );
            $unitPrice = (float) $variant->price;
        }

        $subtotal = $unitPrice * $quantity;
        $couponDeclined = ! empty($data['coupon_declined']);

        if ($couponDeclined) {
            $couponResult = null;
        } else {
            $couponResult = $this->couponService->resolveForOrder(
                $product,
                $customer,
                $subtotal,
                $data['coupon_code'] ?? null,
                isset($data['coupon_id']) ? (int) $data['coupon_id'] : null,
            );
        }

        $discountAmount = $couponResult['discount_amount'] ?? 0;

        $fulfillmentType = (string) ($data['fulfillment_type'] ?? Order::FULFILLMENT_TAKEAWAY);
        $deliveryCharge = $this->resolveDeliveryCharge($fulfillmentType, $product, $variant);

        return [
            'amount' => max(0, $subtotal - $discountAmount) + $deliveryCharge,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'delivery_charge' => $deliveryCharge,
            'unit_price' => $unitPrice,
            'variant' => $variant,
            'coupon_result' => $couponResult,
        ];
    }

    /**
     * Delivery charge: the selected variant's weight has its own charge (set on the
     * Cake weight), else the product's own flat delivery charge (only relevant for
     * products without variants), else the site-wide default from Settings.
     */
    private function resolveDeliveryCharge(string $fulfillmentType, Product $product, ?ProductVariant $variant): float
    {
        if ($fulfillmentType !== Order::FULFILLMENT_DELIVERY) {
            return 0.0;
        }

        $charge = $variant !== null
            ? $this->productVariantService->weightValueForVariant($variant)?->delivery_charge
            : $product->delivery_charge;

        return $charge !== null ? (float) $charge : (float) (settings('default_delivery_charge') ?? 0);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{
     *     amount: float,
     *     subtotal: float,
     *     discount_amount: float,
     *     delivery_charge: float,
     *     unit_price: float,
     *     variant: \App\Models\ProductVariant|null,
     *     coupon_result: array{coupon: \App\Models\Coupon, discount_amount: float, label: string}|null
     * }  $pricing
     */
    private function buildOrderFromPricing(Product $product, array $data, ?User $customer, array $pricing): Order
    {
        $order = new Order;
        $order->user_id = $customer?->id;
        $order->guest_name = $data['guest_name'] ?? '';
        $order->guest_email = $data['guest_email'] ?? null;
        $order->guest_phone = $data['guest_phone'] ?? '';
        $order->product_id = $product->id;
        $order->product_name = $product->name_en;
        $order->product_sku = $product->sku;
        $order->quantity = (int) ($data['quantity'] ?? 1);
        $order->message_on_cake = $data['message_on_cake'] ?? null;
        $order->instructions = $data['instructions'] ?? null;
        $order->fulfillment_type = $data['fulfillment_type'] ?? Order::FULFILLMENT_TAKEAWAY;
        $order->delivery_address = $order->fulfillment_type === Order::FULFILLMENT_DELIVERY
            ? ($data['delivery_address'] ?? null)
            : null;
        $order->delivery_pincode = $this->resolveDeliveryPincode($order->fulfillment_type, $data['delivery_pincode'] ?? null);
        $order->order_status = 'pending';

        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $order->delivery_at = Carbon::parse($data['delivery_at'], $tz)->utc();
        $order->ordered_at = now();

        $this->applyFlavorSnapshot($order, $product, $data['flavor_id'] ?? null);

        if ($pricing['variant'] !== null) {
            $order->product_variant_id = $pricing['variant']->id;
        }

        $order->unit_price = $pricing['unit_price'];
        $order->subtotal = $pricing['subtotal'];
        $order->discount_amount = $pricing['discount_amount'];
        $order->delivery_charge = $pricing['delivery_charge'];
        $order->amount = $pricing['amount'];

        $couponResult = $pricing['coupon_result'];
        if ($couponResult !== null) {
            $order->coupon_id = $couponResult['coupon']->id;
            $order->coupon_code = $couponResult['coupon']->code;
            $order->coupon_label = $couponResult['coupon']->label;
        }

        return $order;
    }

    private function resolveDeliveryPincode(string $fulfillmentType, mixed $rawPincode): ?string
    {
        if ($fulfillmentType !== Order::FULFILLMENT_DELIVERY) {
            return null;
        }

        $normalized = $this->pincodeService->normalize((string) ($rawPincode ?? ''));

        if ($normalized === null || ! $this->pincodeService->isServiceable($normalized)) {
            throw ValidationException::withMessages([
                'delivery_pincode' => [__('Sorry, we do not deliver to this pincode yet. Please choose Take away or contact us.')],
            ]);
        }

        return $normalized;
    }

    private function applyFlavorSnapshot(Order $order, Product $product, mixed $flavorId): void
    {
        if ($flavorId === null || $flavorId === '') {
            return;
        }

        $flavor = $product->flavors()->active()->whereKey((int) $flavorId)->first();
        if (! $flavor) {
            return;
        }

        $order->flavor_id = $flavor->id;
        $order->flavor_name = $flavor->name_en;
        $order->flavor_slug = $flavor->slug;
    }

    public function submitPaymentDetails(Order $order, array $data): void
    {
        $order->payment_reference = $data['payment_reference'] ?? null;
        $order->payment_amount = isset($data['payment_amount']) ? (float) $data['payment_amount'] : null;
        $order->payment_made_at = isset($data['payment_made_at']) ? Carbon::parse($data['payment_made_at'])->utc() : null;
        $order->save();
    }

    public function verifyPayment(Order $order): void
    {
        $order->payment_status = Order::PAYMENT_STATUS_VERIFIED;
        $order->save();
    }

    public function recordInStoreCashPayment(Order $order, float $amountReceived): void
    {
        if (! $order->isInStoreOrder()) {
            throw ValidationException::withMessages([
                'amount_received' => [__('Cash payments can only be recorded for in-store orders.')],
            ]);
        }

        if (! $order->hasOutstandingBalance()) {
            throw ValidationException::withMessages([
                'amount_received' => [__('This order is already fully paid.')],
            ]);
        }

        $balanceDue = $order->balanceDue();

        if ($amountReceived < 0.01) {
            throw ValidationException::withMessages([
                'amount_received' => [__('Enter an amount greater than zero.')],
            ]);
        }

        if ($amountReceived > $balanceDue + 0.01) {
            throw ValidationException::withMessages([
                'amount_received' => [__('Amount cannot exceed the balance due of :amount.', [
                    'amount' => '₹ '.number_format($balanceDue, 2),
                ])],
            ]);
        }

        $newTotal = round($order->totalCashReceived() + $amountReceived, 2);
        $order->payment_amount = min($newTotal, (float) $order->amount);
        $order->payment_made_at = now();
        $order->payment_status = Order::PAYMENT_STATUS_VERIFIED;
        $order->save();
    }

    private function applyInStoreCashReceived(Order $order, float $cashReceived): void
    {
        $cash = min(max(0, $cashReceived), (float) $order->amount);
        $order->payment_amount = $cash;
        $order->payment_made_at = $cash > 0 ? now() : null;
        $order->payment_status = Order::PAYMENT_STATUS_VERIFIED;
    }

    public function updateOrderStatus(Order $order, string $orderStatus, ?string $preparationAt = null): void
    {
        if ($order->isStatusLocked()) {
            throw ValidationException::withMessages([
                'order_status' => [__('This order cannot be changed.')],
            ]);
        }

        if ($orderStatus === Order::STATUS_DELIVERED) {
            if (! $order->isDeliveryFulfillment()) {
                throw ValidationException::withMessages([
                    'order_status' => [__('Delivered status is only available for delivery orders.')],
                ]);
            }

            if ($order->order_status !== 'completed') {
                throw ValidationException::withMessages([
                    'order_status' => [__('Order must be completed before marking as delivered.')],
                ]);
            }
        }

        $order->order_status = $orderStatus;

        if ($orderStatus === 'processing' && $preparationAt !== null) {
            $tz = settings('timezone') ?? 'Asia/Kolkata';
            $order->preparation_at = Carbon::parse($preparationAt, $tz)->utc();
        } elseif ($orderStatus !== 'processing') {
            $order->preparation_at = null;
        }

        $order->save();
    }

    /**
     * @return array{min: \Carbon\Carbon, max: \Carbon\Carbon|null, timezone: string}
     */
    public function preparationAtRules(Order $order): array
    {
        $timezone = settings('timezone') ?? 'Asia/Kolkata';
        $now = Carbon::now($timezone);
        $min = $now->copy()->subMinutes(5);

        $max = null;
        $deliveryPast = false;
        if ($order->delivery_at) {
            $deliveryInTz = $order->delivery_at->copy()->setTimezone($timezone);
            $deliveryPast = $deliveryInTz->isPast();
            if (! $deliveryPast) {
                $max = $deliveryInTz;
            }
        }

        return [
            'min' => $min,
            'max' => $max,
            'timezone' => $timezone,
            'deliveryPast' => $deliveryPast,
        ];
    }

    public function updateSerialNumber(Order $order, ?string $serialNumber): void
    {
        $order->serial_number = $serialNumber ?: null;
        $order->save();
    }

    public function deliveryAtRules(?\App\Models\Product $product = null): array
    {
        $timezone = settings('timezone') ?? 'Asia/Kolkata';
        $maxDays = (int) (settings('order_max_future_days') ?? 7);
        $minHours = $product !== null
            ? $product->minHoursBeforeDelivery()
            : (int) (settings('order_min_hours_before_delivery') ?? 4);

        $now = Carbon::now($timezone);
        $minDelivery = $now->copy()->addHours($minHours);
        $maxDelivery = $now->copy()->addDays($maxDays)->endOfDay();

        return [
            'after' => $minDelivery->utc(),
            'before' => $maxDelivery->utc(),
            'timezone' => $timezone,
        ];
    }

    public function suggestedDeliveryAt(array $rules): string
    {
        $timezone = $rules['timezone'];
        $min = Carbon::parse($rules['after'])->setTimezone($timezone);

        $minute = (int) $min->format('i');
        $remainder = $minute % 15;
        if ($remainder !== 0) {
            $min->addMinutes(15 - $remainder)->second(0);
        } else {
            $min->second(0);
        }

        return $min->format('Y-m-d\TH:i');
    }

    /**
     * @return array{min: string, max: string, min_display: string, max_display: string, timezone: string}
     */
    public function deliveryAtBoundsForInput(array $rules): array
    {
        $timezone = $rules['timezone'];
        $min = Carbon::parse($rules['after'])->setTimezone($timezone);
        $max = Carbon::parse($rules['before'])->setTimezone($timezone);

        return [
            'min' => $min->format('Y-m-d\TH:i'),
            'max' => $max->format('Y-m-d\TH:i'),
            'min_display' => $min->format('M d, Y · h:i A'),
            'max_display' => $max->format('M d, Y · h:i A'),
            'timezone' => $timezone,
        ];
    }

    public function validateDeliveryAtForProduct(?\App\Models\Product $product, string $deliveryAt): ?string
    {
        $rules = $this->deliveryAtRules($product);
        $bounds = $this->deliveryAtBoundsForInput($rules);
        $timezone = $bounds['timezone'];

        try {
            $selected = Carbon::createFromFormat('Y-m-d\TH:i', $deliveryAt, $timezone);
        } catch (\Throwable) {
            return __('Please enter a valid delivery date and time.');
        }

        $min = Carbon::createFromFormat('Y-m-d\TH:i', $bounds['min'], $timezone);
        $max = Carbon::createFromFormat('Y-m-d\TH:i', $bounds['max'], $timezone);

        if ($selected->lt($min)) {
            return __('Please choose a time on or after :time (:timezone).', [
                'time' => $bounds['min_display'],
                'timezone' => $timezone,
            ]);
        }

        if ($selected->gt($max)) {
            return __('Please choose a time on or before :time (:timezone).', [
                'time' => $bounds['max_display'],
                'timezone' => $timezone,
            ]);
        }

        return null;
    }
}
