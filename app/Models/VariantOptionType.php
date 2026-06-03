<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantOptionType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name_en',
        'selection_mode',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
