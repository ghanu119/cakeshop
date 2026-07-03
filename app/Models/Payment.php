<?php

namespace App\Models;

use App\Services\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_gateway',
        'gateway_order_id',
        'gateway_payment_id',
        'signature',
        'amount',
        'currency',
        'status',
        'response_payload',
        'paid_at',
        'failed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'response_payload' => 'array',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', PaymentStatus::Paid);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::Pending);
    }

    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }
}
