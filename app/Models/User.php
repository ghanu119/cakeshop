<?php

namespace App\Models;

use App\Models\User\RegisteredVia;
use App\Models\User\UserGender;
use App\Services\StaffPushSubscriptionService;
use App\Support\AuthGuards;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Minishlink\WebPush\ContentEncoding;
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, Notifiable, SoftDeletes;

    public const DELETION_REASON_CUSTOMER = 'customer_request';

    public const DELETION_REASON_ADMIN = 'admin_action';

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if (! $user->isForceDeleting()) {
                $user->releaseEmailForSoftDelete();

                if ($user->isCustomer()) {
                    $user->releasePhoneForSoftDelete();
                }
            }
        });
    }

    public function releaseEmailForSoftDelete(): void
    {
        if ($this->email === null || $this->email === '') {
            return;
        }

        $suffix = '-deleted-'.$this->id;

        if (str_ends_with($this->email, $suffix)) {
            return;
        }

        $maxLength = 255;
        $email = $this->email.$suffix;

        if (strlen($email) > $maxLength) {
            $email = substr($this->email, 0, $maxLength - strlen($suffix)).$suffix;
        }

        $this->email = $email;
        $this->saveQuietly();
    }

    public function releasePhoneForSoftDelete(): void
    {
        if ($this->phone === null || $this->phone === '') {
            return;
        }

        $suffix = '-deleted-'.$this->id;

        if (str_ends_with($this->phone, $suffix)) {
            return;
        }

        $maxLength = 50;
        $phone = $this->phone.$suffix;

        if (strlen($phone) > $maxLength) {
            $phone = substr($this->phone, 0, $maxLength - strlen($suffix)).$suffix;
        }

        $this->phone = $phone;
        $this->saveQuietly();
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'whatsapp_verified_at' => 'datetime',
            'email_claimed_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'purged_at' => 'datetime',
            'password' => 'hashed',
            'birth_day' => 'integer',
            'birth_month' => 'integer',
            'anniversary_day' => 'integer',
            'anniversary_month' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->role('Customer', AuthGuards::STAFF);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->role(['Admin', 'Kitchen'], AuthGuards::STAFF);
    }

    public function scopeActiveCustomers(Builder $query): Builder
    {
        return $query->customers();
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('Customer', AuthGuards::STAFF);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['Admin', 'Kitchen'], AuthGuards::STAFF);
    }

    public function updatePushSubscription(
        string $endpoint,
        ?string $key = null,
        ?string $token = null,
        ContentEncoding|string|null $contentEncoding = null
    ): PushSubscription {
        return app(StaffPushSubscriptionService::class)->upsertForUser(
            $this,
            $endpoint,
            $key,
            $token,
            $contentEncoding
        );
    }

    public function hasEmail(): bool
    {
        return $this->email !== null && $this->email !== '';
    }

    public function isEmailVerified(): bool
    {
        return $this->hasEmail() && $this->email_verified_at !== null;
    }

    public function isWhatsAppVerified(): bool
    {
        return $this->whatsapp_verified_at !== null;
    }

    public function isPhoneOnly(): bool
    {
        return $this->isCustomer() && ! $this->hasEmail() && $this->phone !== null;
    }

    public function genderLabel(): ?string
    {
        if ($this->gender === null) {
            return null;
        }

        return UserGender::options()[$this->gender] ?? $this->gender;
    }

    public function registeredViaLabel(): ?string
    {
        return match ($this->registered_via) {
            RegisteredVia::FRONT_OTP => __('Signed up online'),
            RegisteredVia::ADMIN_CREATED => __('Added by store'),
            default => null,
        };
    }
}
