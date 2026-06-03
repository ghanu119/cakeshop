<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderVariantSelection extends Model
{
    protected $fillable = [
        'order_id',
        'variant_option_type_id',
        'variant_option_type_slug',
        'variant_option_value_id',
        'label',
        'grams',
    ];

    protected function casts(): array
    {
        return [
            'grams' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VariantOptionType::class, 'variant_option_type_id');
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(VariantOptionValue::class, 'variant_option_value_id');
    }
}
