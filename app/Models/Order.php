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

    protected $fillable = [
        'uuid',
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

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($order->ordered_at)) {
                $order->ordered_at = now();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof');
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

    public function scopeVisibleToKitchen($query): void
    {
        $query->where('payment_status', 'verified')
            ->where('order_status', 'processing')
            ->whereNotNull('preparation_at');

        $timezone = settings('timezone') ?? 'Asia/Kolkata';
        $nowShop = Carbon::now($timezone);
        $todayStartUtc = $nowShop->copy()->startOfDay()->utc();
        $todayEndUtc = $nowShop->copy()->endOfDay()->utc();

        $query->whereBetween('delivery_at', [$todayStartUtc, $todayEndUtc]);

        $leadHours = settings('kitchen_lead_hours');
        if ($leadHours !== null && $leadHours !== '') {
            $leadHours = (int) $leadHours;
            $visibleUntilUtc = $nowShop->copy()->addHours($leadHours)->utc();
            $query->where('delivery_at', '<=', $visibleUntilUtc);
        }
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === 'verified';
    }
}
