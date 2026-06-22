<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
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
        private ProductVariantService $productVariantService
    ) {}

    public function listForAdmin(Request $request): LengthAwarePaginator
    {
        $query = Order::query()->with(['product', 'media']);

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
        if ($request->boolean('delivery_today')) {
            $query->deliveryToday();
        }
        if ($request->boolean('awaiting_payment_verification')) {
            $query->awaitingPaymentVerification();
        }

        $this->applyAdminListSorting($query, $request);

        return $query->paginate(15)->withQueryString();
    }

    private function applyAdminListSorting(Builder $query, Request $request): void
    {
        $sort = $request->input('sort', 'ordered_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

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
            ->kitchenTodayQueue()
            ->orderBy('delivery_at')
            ->paginate(20);
    }

    public function listKitchenTodayForDashboard(): Collection
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

    public function createOrder(Product $product, array $data): Order
    {
        $customerContext = app(CustomerContext::class);
        $customer = $customerContext->effectiveCustomer();

        $order = new Order;
        $order->user_id = $customer?->id;
        $order->guest_name = $data['guest_name'] ?? '';
        $order->guest_email = $data['guest_email'] ?? null;
        $order->guest_phone = $data['guest_phone'] ?? '';
        $order->product_id = $product->id;
        $order->product_name = $product->name_en;
        $order->quantity = (int) ($data['quantity'] ?? 1);
        $order->message_on_cake = $data['message_on_cake'] ?? null;
        $order->instructions = $data['instructions'] ?? null;
        $order->fulfillment_type = $data['fulfillment_type'] ?? Order::FULFILLMENT_TAKEAWAY;
        $order->delivery_address = $order->fulfillment_type === Order::FULFILLMENT_DELIVERY
            ? ($data['delivery_address'] ?? null)
            : null;
        $order->payment_status = 'pending';
        $order->order_status = 'pending';
        $order->payment_method = Order::PAYMENT_METHOD_UPI;

        if ($customerContext->isImpersonating()) {
            $order->payment_method = Order::PAYMENT_METHOD_CASH_ON_STORE;
            $order->payment_status = 'verified';
            $order->placed_by_user_id = $customerContext->impersonator()?->id;
        }

        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $order->delivery_at = Carbon::parse($data['delivery_at'], $tz)->utc();
        $order->ordered_at = now();

        $this->applyFlavorSnapshot($order, $product, $data['flavor_id'] ?? null);

        if ($this->productVariantService->hasVariants($product)) {
            $variant = $this->productVariantService->findVariantForProduct(
                $product,
                (int) $data['product_variant_id']
            );
            $order->product_variant_id = $variant->id;
            $order->unit_price = $variant->price;
            $order->amount = $variant->price * $order->quantity;
            $order->save();
            $this->productVariantService->snapshotOrder($product, $variant, $order);
        } else {
            $order->unit_price = $product->price;
            $order->amount = $product->price * $order->quantity;
            $order->save();
        }

        return $order;
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
        $order->payment_status = 'verified';
        $order->save();
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

    public function deliveryAtRules(): array
    {
        $timezone = settings('timezone') ?? 'Asia/Kolkata';
        $maxDays = (int) (settings('order_max_future_days') ?? 7);
        $minHours = (int) (settings('order_min_hours_before_delivery') ?? 4);

        $now = Carbon::now($timezone);
        $minDelivery = $now->copy()->addHours($minHours);
        $maxDelivery = $now->copy()->addDays($maxDays)->endOfDay();

        return [
            'after' => $minDelivery->utc(),
            'before' => $maxDelivery->utc(),
            'timezone' => $timezone,
        ];
    }
}
