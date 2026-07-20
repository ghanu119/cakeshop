<?php

namespace App\Models;

use App\Services\Payments\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Order extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const FULFILLMENT_TAKEAWAY = 'takeaway';

    public const FULFILLMENT_DELIVERY = 'delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const PAYMENT_METHOD_UPI = 'upi';

    public const PAYMENT_METHOD_CASH_ON_STORE = 'cash_on_store';

    public const PAYMENT_METHOD_RAZORPAY = 'razorpay';

    public const PAYMENT_STATUS_PENDING = 'pending';

    public const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';

    public const PAYMENT_STATUS_VERIFIED = 'verified';

    private const PAYMENT_TOLERANCE = 0.01;

    /** Default max inscription length when no site or product override is set. */
    public const MESSAGE_ON_CAKE_MAX_LENGTH = 50;

    public const MESSAGE_ON_CAKE_MIN_LENGTH = 5;

    public const MESSAGE_ON_CAKE_LIMIT_MAX = 100;

    public static function defaultMessageOnCakeMaxLength(): int
    {
        $value = settings('message_on_cake_max_length');

        if ($value === null || $value === '') {
            return self::MESSAGE_ON_CAKE_MAX_LENGTH;
        }

        return self::clampMessageOnCakeLimit((int) $value);
    }

    public static function clampMessageOnCakeLimit(int $length): int
    {
        return max(self::MESSAGE_ON_CAKE_MIN_LENGTH, min(self::MESSAGE_ON_CAKE_LIMIT_MAX, $length));
    }

    protected $fillable = [
        'uuid',
        'order_no',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'unit_price',
        'variant_summary',
        'weight_grams',
        'flavor_id',
        'flavor_name',
        'flavor_slug',
        'quantity',
        'message_on_cake',
        'instructions',
        'fulfillment_type',
        'delivery_address',
        'delivery_pincode',
        'serial_number',
        'amount',
        'coupon_id',
        'coupon_code',
        'coupon_label',
        'subtotal',
        'discount_amount',
        'payment_status',
        'payment_method',
        'order_status',
        'payment_reference',
        'payment_amount',
        'payment_made_at',
        'delivery_at',
        'preparation_at',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'weight_grams' => 'integer',
            'payment_amount' => 'decimal:2',
            'payment_made_at' => 'datetime',
            'delivery_at' => 'datetime',
            'preparation_at' => 'datetime',
            'ordered_at' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($order->ordered_at)) {
                $order->ordered_at = now();
            }
            if (empty($order->order_no)) {
                $order->order_no = app(\App\Services\OrderNumberService::class)->assignNext($order);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->keepOriginalImageFormat();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function hasDiscount(): bool
    {
        return (float) ($this->discount_amount ?? 0) > 0;
    }

    public function displaySubtotal(): float
    {
        if ($this->subtotal !== null) {
            return (float) $this->subtotal;
        }

        return (float) $this->amount + (float) ($this->discount_amount ?? 0);
    }

    public function hasDistinctContactFromAccount(): bool
    {
        if ($this->user_id === null) {
            return false;
        }

        $this->loadMissing('user');

        if ($this->user === null) {
            return false;
        }

        $contactName = trim((string) $this->guest_name);
        $contactEmail = strtolower(trim((string) ($this->guest_email ?? '')));
        $contactPhone = \App\Support\PhoneNormalizer::normalize((string) $this->guest_phone) ?? trim((string) $this->guest_phone);

        $accountName = trim((string) $this->user->name);
        $accountEmail = strtolower(trim((string) ($this->user->email ?? '')));
        $accountPhone = \App\Support\PhoneNormalizer::normalize((string) $this->user->phone) ?? trim((string) ($this->user->phone ?? ''));

        return $contactName !== $accountName
            || $contactEmail !== $accountEmail
            || $contactPhone !== $accountPhone;
    }

    public function placedBy()
    {
        return $this->belongsTo(User::class, 'placed_by_user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paidPayment(): ?Payment
    {
        if ($this->relationLoaded('payments')) {
            return $this->payments
                ->filter(fn (Payment $payment) => $payment->status === PaymentStatus::Paid)
                ->sortByDesc(fn (Payment $payment) => $payment->paid_at ?? $payment->created_at)
                ->first();
        }

        return $this->payments()->paid()->latest('paid_at')->first();
    }

    public function displayPaymentReference(): ?string
    {
        if (filled($this->payment_reference)) {
            return (string) $this->payment_reference;
        }

        return $this->paidPayment()?->gateway_payment_id;
    }

    public function displayPaymentAmount(): ?float
    {
        if ($this->payment_amount !== null) {
            return (float) $this->payment_amount;
        }

        $payment = $this->paidPayment();

        return $payment !== null ? (float) $payment->amount : null;
    }

    public function displayPaymentMadeAt(): ?Carbon
    {
        if ($this->payment_made_at !== null) {
            return $this->payment_made_at;
        }

        return $this->paidPayment()?->paid_at;
    }

    public function hasDisplayablePaymentDetails(): bool
    {
        return $this->displayPaymentReference() !== null
            || $this->displayPaymentAmount() !== null
            || $this->displayPaymentMadeAt() !== null
            || $this->hasMedia('payment_proof');
    }

    public function isRazorpayPayment(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_RAZORPAY;
    }

    public function isCashOnStore(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_CASH_ON_STORE;
    }

    public function isInStoreOrder(): bool
    {
        return $this->isCashOnStore() || $this->placed_by_user_id !== null;
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_METHOD_CASH_ON_STORE => __('Cash on store'),
            self::PAYMENT_METHOD_RAZORPAY => __('Razorpay'),
            self::PAYMENT_METHOD_UPI => __('UPI'),
            default => __('UPI'),
        };
    }

    public function totalCashReceived(): float
    {
        if ($this->payment_amount !== null) {
            return (float) $this->payment_amount;
        }

        return 0.0;
    }

    public function balanceDue(): float
    {
        return max(0.0, round((float) $this->amount - $this->totalCashReceived(), 2));
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->isInStoreOrder() && $this->balanceDue() > self::PAYMENT_TOLERANCE;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIALLY_PAID;
    }

    public function isInStorePaymentComplete(): bool
    {
        return $this->isInStoreOrder() && ! $this->hasOutstandingBalance();
    }

    public function isVerifiedWithOutstandingBalance(): bool
    {
        return $this->isInStoreOrder()
            && $this->isPaymentVerified()
            && $this->hasOutstandingBalance();
    }

    public function requiresPaymentBeforeStatusChange(): bool
    {
        return ! $this->isInStoreOrder();
    }

    public function adminPaymentStatusLabel(): string
    {
        if ($this->isInStoreOrder()) {
            if ($this->isVerifiedWithOutstandingBalance()) {
                return __('Payment verified — :amount due', [
                    'amount' => '₹ '.number_format($this->balanceDue(), 2),
                ]);
            }

            if ($this->isPaymentVerified()) {
                return __('Cash on store — fully collected');
            }

            if ($this->isPartiallyPaid()) {
                return __('Partially paid — :amount due', [
                    'amount' => '₹ '.number_format($this->balanceDue(), 2),
                ]);
            }

            return __('Payment pending — pay on pickup');
        }

        if ($this->isPaymentVerified()) {
            return __('Payment verified');
        }

        if ($this->hasPaymentDetailsSubmitted()) {
            return __('Awaiting verification');
        }

        return __('Payment pending');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }

    public function variantSelections()
    {
        return $this->hasMany(OrderVariantSelection::class);
    }

    public function flavor()
    {
        return $this->belongsTo(Flavor::class)->withTrashed();
    }

    public function hasFlavorSnapshot(): bool
    {
        return $this->flavor_name !== null && $this->flavor_name !== '';
    }

    public function displayFlavorName(): string
    {
        if ($this->flavor_name) {
            return $this->flavor_name;
        }

        if ($this->flavor) {
            return $this->flavor->displayName();
        }

        return '';
    }

    public function displayProductName(): string
    {
        if ($this->product_name) {
            return $this->product_name;
        }

        if ($this->product) {
            return $this->product->name_en;
        }

        return __('Product');
    }

    public function displayProductSku(): ?string
    {
        if ($this->product_sku) {
            return $this->product_sku;
        }

        return $this->product?->sku;
    }

    public function displayUnitPrice(): float
    {
        if ($this->unit_price !== null) {
            return (float) $this->unit_price;
        }

        $qty = max(1, (int) $this->quantity);

        return (float) $this->amount / $qty;
    }

    public function hasVariantSnapshot(): bool
    {
        return $this->variant_summary !== null && $this->variant_summary !== '';
    }

    public function isProcessing(): bool
    {
        return $this->order_status === 'processing';
    }

    public function hasPreparationDeadline(): bool
    {
        return $this->preparation_at !== null;
    }

    public static function shopTimezone(): string
    {
        return settings('timezone') ?? 'Asia/Kolkata';
    }

    /**
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon, now: \Carbon\Carbon}
     */
    public static function todayBoundsInShopTz(): array
    {
        $timezone = self::shopTimezone();
        $nowShop = Carbon::now($timezone);

        return [
            'start' => $nowShop->copy()->startOfDay()->utc(),
            'end' => $nowShop->copy()->endOfDay()->utc(),
            'now' => $nowShop,
        ];
    }

    /**
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon}
     */
    public static function weekBoundsInShopTz(): array
    {
        $timezone = self::shopTimezone();
        $nowShop = Carbon::now($timezone);

        return [
            'start' => $nowShop->copy()->startOfWeek()->utc(),
            'end' => $nowShop->copy()->endOfWeek()->utc(),
        ];
    }

    public function scopePaymentVerified($query): void
    {
        $query->where('payment_status', self::PAYMENT_STATUS_VERIFIED);
    }

    public function scopeInStoreOutstanding($query): void
    {
        $query->inStore()
            ->whereRaw('COALESCE(payment_amount, 0) + ? < amount', [self::PAYMENT_TOLERANCE]);
    }

    public function scopeInStore($query): void
    {
        $query->where(function ($q) {
            $q->where('payment_method', self::PAYMENT_METHOD_CASH_ON_STORE)
                ->orWhereNotNull('placed_by_user_id');
        });
    }

    public function scopeKitchenPaymentEligible($query): void
    {
        $query->where(function ($q) {
            $q->where('payment_status', self::PAYMENT_STATUS_VERIFIED)
                ->orWhere('payment_method', self::PAYMENT_METHOD_CASH_ON_STORE)
                ->orWhereNotNull('placed_by_user_id');
        });
    }

    public function scopeDeliveryToday($query): void
    {
        $bounds = self::todayBoundsInShopTz();
        $query->whereBetween('delivery_at', [$bounds['start'], $bounds['end']]);
    }

    public function scopeDeliveryUpcoming($query): void
    {
        $bounds = self::todayBoundsInShopTz();
        $query->where('delivery_at', '>', $bounds['end']);
    }

    public function scopePreparedByUpcoming($query): void
    {
        $bounds = self::todayBoundsInShopTz();
        $query->where('preparation_at', '>', $bounds['end']);
    }

    /**
     * Today's orders visible to kitchen: payment verified, delivery today, still active.
     * Includes orders awaiting admin to set Processing + preparation time.
     */
    public function scopeKitchenTodayVisible($query): void
    {
        $query->kitchenPaymentEligible()
            ->deliveryToday()
            ->whereIn('order_status', ['pending', 'processing']);
    }

    /**
     * Today's orders kitchen staff can update (processing, prep time set, within lead window).
     */
    public function scopeKitchenTodayQueue($query): void
    {
        $query->kitchenPaymentEligible()
            ->deliveryToday()
            ->where('order_status', 'processing')
            ->whereNotNull('preparation_at');

        $bounds = self::todayBoundsInShopTz();
        $leadHours = settings('kitchen_lead_hours');
        if ($leadHours !== null && $leadHours !== '') {
            $visibleUntilUtc = $bounds['now']->copy()->addHours((int) $leadHours)->utc();
            $query->where('preparation_at', '<=', $visibleUntilUtc);
        }
    }

    public function scopeKitchenUpcoming($query): void
    {
        $query->kitchenPaymentEligible()
            ->deliveryUpcoming()
            ->whereIn('order_status', ['pending', 'processing']);
    }

    public function scopeAwaitingPaymentVerification($query): void
    {
        $query->where('payment_status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('payment_reference')
                    ->orWhereNotNull('payment_amount')
                    ->orWhereNotNull('payment_made_at')
                    ->orWhereHas('media', fn ($m) => $m->where('collection_name', 'payment_proof'));
            });
    }

    public function scopeOrderedThisWeek($query): void
    {
        $bounds = self::weekBoundsInShopTz();
        $query->whereBetween('ordered_at', [$bounds['start'], $bounds['end']]);
    }

    public function scopeVisibleToKitchen($query): void
    {
        $query->kitchenTodayVisible();
    }

    public function isAwaitingKitchenSetup(): bool
    {
        return $this->isKitchenPaymentEligible()
            && $this->isDeliveryToday()
            && in_array($this->order_status, ['pending', 'processing'], true)
            && (! $this->isProcessing() || ! $this->hasPreparationDeadline());
    }

    public function isKitchenPaymentEligible(): bool
    {
        if ($this->isPaymentVerified()) {
            return true;
        }

        return $this->isInStoreOrder();
    }

    public function isDeliveryToday(): bool
    {
        if (! $this->delivery_at) {
            return false;
        }

        $bounds = self::todayBoundsInShopTz();

        return $this->delivery_at->between($bounds['start'], $bounds['end']);
    }

    public function isKitchenActionable(): bool
    {
        if (! $this->isKitchenPaymentEligible()
            || ! $this->isProcessing()
            || ! $this->hasPreparationDeadline()
            || ! $this->isDeliveryToday()) {
            return false;
        }

        $leadHours = settings('kitchen_lead_hours');
        if ($leadHours === null || $leadHours === '') {
            return true;
        }

        $bounds = self::todayBoundsInShopTz();
        $nowUtc = $bounds['now']->copy()->utc();

        if ($this->preparation_at->lte($nowUtc)) {
            return true;
        }

        $visibleUntilUtc = $bounds['now']->copy()->addHours((int) $leadHours)->utc();

        return $this->preparation_at <= $visibleUntilUtc;
    }

    public function canKitchenUpdateStatus(): bool
    {
        return $this->isKitchenActionable();
    }

    public function daysUntilDelivery(): ?int
    {
        if (! $this->delivery_at) {
            return null;
        }

        $tz = self::shopTimezone();
        $today = Carbon::now($tz)->startOfDay();
        $deliveryDay = $this->delivery_at->copy()->setTimezone($tz)->startOfDay();

        return max(0, (int) $today->diffInDays($deliveryDay, false));
    }

    public function daysUntilDeliveryLabel(): string
    {
        $days = $this->daysUntilDelivery();

        if ($days === null) {
            return '—';
        }

        if ($days === 0) {
            return __('Today');
        }

        if ($days === 1) {
            return __('1 day left');
        }

        return __(':count days left', ['count' => $days]);
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_VERIFIED;
    }

    public function hasPaymentDetailsSubmitted(): bool
    {
        if ($this->payment_reference || $this->payment_amount !== null || $this->payment_made_at !== null) {
            return true;
        }

        return $this->hasMedia('payment_proof');
    }

    public function paymentStatusBadgeLabel(): string
    {
        if ($this->isInStoreOrder()) {
            if ($this->isVerifiedWithOutstandingBalance()) {
                return __('Verified — due');
            }

            if ($this->isPaymentVerified()) {
                return __('Paid');
            }

            if ($this->isPartiallyPaid()) {
                return __('Partially paid');
            }

            return __('Payment pending');
        }

        if ($this->isPaymentVerified()) {
            return __('Paid');
        }

        if ($this->hasPaymentDetailsSubmitted()) {
            return __('Awaiting verification');
        }

        return __('Payment pending');
    }

    public function inStorePaymentListBadgeLabel(): string
    {
        if ($this->isVerifiedWithOutstandingBalance()) {
            return __('Verified — :amount due', [
                'amount' => '₹'.number_format($this->balanceDue(), 2),
            ]);
        }

        if ($this->isPaymentVerified()) {
            return __('In-store — paid');
        }

        if ($this->isPartiallyPaid()) {
            return __('Partial — :amount due', [
                'amount' => '₹'.number_format($this->balanceDue(), 2),
            ]);
        }

        return __('Pay later');
    }

    public function isTakeaway(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_TAKEAWAY;
    }

    public function isDeliveryFulfillment(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_DELIVERY;
    }

    public function isDelivered(): bool
    {
        return $this->order_status === self::STATUS_DELIVERED;
    }

    public function isStatusLocked(): bool
    {
        return $this->isDelivered();
    }

    public function fulfillmentLabel(): string
    {
        return match ($this->fulfillment_type) {
            self::FULFILLMENT_DELIVERY => __('Delivery'),
            self::FULFILLMENT_TAKEAWAY => __('Take away'),
            default => __('Take away'),
        };
    }

    public function orderStatusLabel(): string
    {
        return match ($this->order_status) {
            'processing' => __('Processing'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            'delivered' => __('Delivered'),
            default => __('Pending'),
        };
    }

    public function paymentStatusLabel(): string
    {
        return $this->paymentStatusBadgeLabel();
    }

    public function customerOrderUrl(): string
    {
        return route('order.confirm', $this);
    }

    /**
     * Dynamic URL segment for WhatsApp template buttons (Meta {{1}}; site path prefix is in the approved template).
     */
    public function customerOrderWhatsAppUrlSuffix(): string
    {
        return (string) $this->uuid;
    }

    /**
     * Third body variable for WhatsApp order templates (estimated delivery datetime only).
     */
    public function whatsappDeliveryTimeLine(): string
    {
        if (! $this->delivery_at) {
            return (string) __('To be confirmed');
        }

        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $deliveryAt = $this->delivery_at->copy()->setTimezone($tz);
        $line = $deliveryAt->format('d M Y').', '.$deliveryAt->format('H:i');

        return strlen($line) > 100 ? substr($line, 0, 97).'...' : $line;
    }

    public function adminOrderUrl(): string
    {
        return route('admin.orders.show', $this);
    }

    public function orderStatusBadgeVariant(): string
    {
        return match ($this->order_status) {
            'completed', 'delivered' => 'success',
            'processing' => 'primary',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    public function paymentStatusBadgeVariant(): string
    {
        if ($this->isInStoreOrder()) {
            if ($this->isPaymentVerified()) {
                return 'success';
            }

            if ($this->isPartiallyPaid()) {
                return 'warning';
            }

            return 'warning';
        }

        if ($this->isPaymentVerified()) {
            return 'success';
        }

        if ($this->hasPaymentDetailsSubmitted()) {
            return 'primary';
        }

        return 'warning';
    }
}
