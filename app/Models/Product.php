<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            if (! $product->isForceDeleting()) {
                $product->releaseSlugForSoftDelete();
            }
        });
    }

    public function releaseSlugForSoftDelete(): void
    {
        $suffix = '-deleted-'.$this->id;

        if (! str_ends_with($this->slug, $suffix)) {
            $this->slug = $this->slug.$suffix;
            $this->saveQuietly();
        }
    }

    protected $fillable = [
        'category_id',
        'name_en',
        'name_hi',
        'name_gu',
        'slug',
        'description_en',
        'description_hi',
        'description_gu',
        'ingredients',
        'short_description',
        'message_on_cake_max_length',
        'price',
        'status',
        'meta_title',
        'meta_description',
        'show_on_homepage',
        'is_highlight',
        'is_trending',
        'is_featured',
        'homepage_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'show_on_homepage' => 'boolean',
            'is_highlight' => 'boolean',
            'is_trending' => 'boolean',
            'is_featured' => 'boolean',
            'homepage_sort_order' => 'integer',
            'message_on_cake_max_length' => 'integer',
        ];
    }

    public function messageOnCakeMaxLength(): int
    {
        if ($this->message_on_cake_max_length !== null) {
            return Order::clampMessageOnCakeLimit((int) $this->message_on_cake_max_length);
        }

        return Order::defaultMessageOnCakeMaxLength();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $collections = ['product_images'];

        $this->addMediaConversion('thumb')
            ->width(150)
            ->keepOriginalImageFormat()
            ->performOnCollections(...$collections)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(400)
            ->keepOriginalImageFormat()
            ->performOnCollections(...$collections)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(800)
            ->keepOriginalImageFormat()
            ->performOnCollections(...$collections)
            ->nonQueued();
    }

    /**
     * @return Collection<int, Media>
     */
    public function orderedProductImages(): Collection
    {
        return $this->getMedia('product_images')->sortBy('order_column')->values();
    }

    public function productImageUrl(Media $media, string $conversion = ''): string
    {
        if ($conversion !== '' && $media->hasGeneratedConversion($conversion)) {
            return $media->getUrl($conversion);
        }

        return $media->getUrl();
    }

    public function primaryProductImageUrl(string $conversion = 'large'): ?string
    {
        $media = $this->orderedProductImages()->first();

        if (! $media) {
            return null;
        }

        return $this->productImageUrl($media, $conversion);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function flavors()
    {
        return $this->belongsToMany(Flavor::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function hasFlavors(): bool
    {
        if ($this->relationLoaded('flavors')) {
            return $this->flavors->where('status', 'active')->isNotEmpty();
        }

        return $this->flavors()->active()->exists();
    }

    public function hasVariants(): bool
    {
        return app(\App\Services\ProductVariantService::class)->hasVariants($this);
    }

    public function syncStartingPrice(): void
    {
        app(\App\Services\ProductVariantService::class)->syncStartingPrice($this);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithFlavorIds($query, array $flavorIds)
    {
        $ids = array_values(array_filter(array_map('intval', $flavorIds)));

        if ($ids === []) {
            return $query;
        }

        return $query->whereHas('flavors', fn ($q) => $q->active()->whereIn('flavors.id', $ids));
    }

    public function scopeWithWeightValueIds($query, array $weightIds)
    {
        $ids = array_values(array_filter(array_map('intval', $weightIds)));

        if ($ids === []) {
            return $query;
        }

        return $query->whereHas('variants', function ($v) use ($ids) {
            $v->active()->whereHas('selections', fn ($s) => $s
                ->whereIn('variant_option_value_id', $ids)
                ->whereHas('type', fn ($t) => $t->where('slug', 'weight')));
        });
    }

    public function scopePriceInRange($query, ?float $min, ?float $max)
    {
        if ($min === null && $max === null) {
            return $query;
        }

        return $query->where(function ($q) use ($min, $max) {
            $q->whereHas('variants', function ($v) use ($min, $max) {
                $v->active()
                    ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
                    ->when($max !== null, fn ($q) => $q->where('price', '<=', $max));
            })->orWhere(function ($q) use ($min, $max) {
                $q->whereDoesntHave('variants', fn ($v) => $v->active())
                    ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
                    ->when($max !== null, fn ($q) => $q->where('price', '<=', $max));
            });
        });
    }

    public function scopeHighlight($query)
    {
        return $query->where('is_highlight', true)->active()->orderBy('homepage_sort_order');
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true)->active()->orderBy('homepage_sort_order');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->active()->orderBy('homepage_sort_order');
    }

    /**
     * Related products: same category first, random order for variety.
     * Excludes the given product.
     */
    public function scopeRelated($query, Product $product, int $limit = 6)
    {
        $query->where('id', '!=', $product->id)->active();

        if ($product->category_id) {
            $query->where('category_id', $product->category_id);
        }

        return $query->inRandomOrder()->limit($limit);
    }

    /**
     * Fallback: other active products (e.g. when same-category has fewer than needed).
     * Excludes given product and given IDs, random order.
     */
    public function scopeExceptIds($query, Product $product, array $excludeIds = []): \Illuminate\Database\Eloquent\Builder
    {
        $ids = array_merge([$product->id], $excludeIds);

        return $query->whereNotIn('id', $ids)->active()->inRandomOrder();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
