<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantOptionValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'variant_option_type_id',
        'label',
        'grams',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'grams' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VariantOptionType::class, 'variant_option_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTypeSlug($query, string $slug)
    {
        return $query->whereHas('type', fn ($q) => $q->where('slug', $slug));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
