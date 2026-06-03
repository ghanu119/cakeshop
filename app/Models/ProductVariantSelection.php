<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantSelection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'variant_option_type_id',
        'variant_option_value_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
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
