<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SliderItem extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const SLIDE_WIDTH = 1690;

    public const SLIDE_HEIGHT = 790;

    protected $fillable = [
        'slider_id',
        'type',
        'title',
        'link',
        'video_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'slider_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slide_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->keepOriginalImageFormat()
            ->performOnCollections('slide_image')
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(self::SLIDE_WIDTH)
            ->keepOriginalImageFormat()
            ->performOnCollections('slide_image')
            ->nonQueued();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function hasImage(): bool
    {
        return $this->isImage() && $this->hasMedia('slide_image');
    }

    public function hasContent(): bool
    {
        if ($this->isVideo()) {
            return filled($this->video_url);
        }

        return $this->hasImage();
    }

    public function imageUrl(string $conversion = 'large'): string
    {
        $url = $this->getFirstMediaUrl('slide_image', $conversion);

        if ($url === '' && $conversion !== '') {
            $url = $this->getFirstMediaUrl('slide_image');
        }

        return $url;
    }

    public function slideImageMedia(): ?Media
    {
        return $this->getFirstMedia('slide_image');
    }
}
