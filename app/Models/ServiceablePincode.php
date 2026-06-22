<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceablePincode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pincode',
        'locality',
        'city',
        'state',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPincode($query, string $normalized)
    {
        return $query->where('pincode', $normalized);
    }

    public function scopeSearch($query, ?string $term)
    {
        if ($term === null || $term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('pincode', 'like', "%{$term}%")
                ->orWhere('locality', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%");
        });
    }

    public function displayLabel(): string
    {
        if ($this->locality) {
            return "{$this->pincode} · {$this->locality}";
        }

        return $this->pincode;
    }
}
