<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLoginOtp extends Model
{
    public const PURPOSE_LOGIN = 'login';

    public const MAX_ATTEMPTS = 5;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'code_hash',
        'purpose',
        'expires_at',
        'attempts',
        'consumed_at',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }
}
