<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    public const DISCOUNT_PERCENTAGE = 'percentage';

    public const DISCOUNT_FIXED = 'fixed';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const PRODUCT_SCOPE_ALL = 'all';

    public const PRODUCT_SCOPE_PRODUCTS = 'products';

    public const PRODUCT_SCOPE_CATEGORIES = 'categories';

    public const USER_SCOPE_ALL = 'all';

    public const USER_SCOPE_USERS = 'users';

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'discount_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'auto_apply' => 'boolean',
            'is_secret' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isWithinDateRange(?Carbon $at = null): bool
    {
        $timezone = settings('timezone') ?? config('app.timezone');
        $date = ($at ?? now($timezone))->timezone($timezone)->toDateString();

        return $date >= $this->from_date->toDateString()
            && $date <= $this->to_date->toDateString();
    }

    public function isUniversal(): bool
    {
        return $this->product_scope === self::PRODUCT_SCOPE_ALL
            && $this->user_scope === self::USER_SCOPE_ALL;
    }
}
