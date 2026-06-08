<?php

namespace App\Models;

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
        'serial_number',
        'amount',
        'payment_status',
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
        $query->where('payment_status', 'verified');
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

    public function scopeKitchenTodayQueue($query): void
    {
        $query->paymentVerified()
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
        $query->paymentVerified()->where('order_status', 'pending');
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
        $query->kitchenTodayQueue();
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
        if (! $this->isPaymentVerified()
            || ! $this->isProcessing()
            || ! $this->hasPreparationDeadline()) {
            return false;
        }

        $leadHours = settings('kitchen_lead_hours');
        if ($leadHours === null || $leadHours === '') {
            return true;
        }

        $bounds = self::todayBoundsInShopTz();

        return $bounds['now'] <= $this->preparation_at;

        // $visibleUntilUtc = $bounds['now']->copy()->addHours((int) $leadHours)->utc();
        // return $this->delivery_at <= $visibleUntilUtc;
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
        return $this->payment_status === 'verified';
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
        if ($this->isPaymentVerified()) {
            return __('Paid');
        }

        if ($this->hasPaymentDetailsSubmitted()) {
            return __('Awaiting verification');
        }

        return __('Payment pending');
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
        if ($this->isPaymentVerified()) {
            return 'success';
        }

        if ($this->hasPaymentDetailsSubmitted()) {
            return 'primary';
        }

        return 'warning';
    }
}
