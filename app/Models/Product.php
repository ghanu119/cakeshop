<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

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
        ];
    }

    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->keepOriginalImageFormat();
        $this->addMediaConversion('medium')->width(400)->keepOriginalImageFormat();
        $this->addMediaConversion('large')->width(800)->keepOriginalImageFormat();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
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
