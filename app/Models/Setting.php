<?php

namespace App\Models;

use App\Support\SecretMask;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'app_settings';

    public const ENCRYPTED_KEYS = [
        'pusher_app_id',
        'pusher_app_key',
        'pusher_app_secret',
        'pusher_app_cluster',
        'webpush_public_key',
        'webpush_private_key',
        'razorpay_key_id',
        'razorpay_key_secret',
    ];

    public const PAYMENT_DEFAULTS = [
        'payment_gateway' => 'razorpay',
        'razorpay_key_id' => null,
        'razorpay_key_secret' => null,
    ];

    public const NOTIFICATION_DEFAULTS = [
        'notifications_enabled' => '1',
        'notifications_web_push_enabled' => '0',
        'pusher_app_id' => null,
        'pusher_app_key' => null,
        'pusher_app_secret' => null,
        'pusher_app_cluster' => null,
        'webpush_subject' => null,
        'webpush_public_key' => null,
        'webpush_private_key' => null,
    ];

    public const DEFAULTS = [
        'site_name' => 'Cake Shop',
        'theme' => null,
        'address' => '',
        'contact' => '',
        'admin_email' => null,
        'google_map_iframe' => null,
        'opening_hours' => null,
        'payment_instructions' => '',
        'payment_upi_id' => '',
        'payment_submit_instructions' => 'Share your transaction/UPI reference number, amount paid, and time of payment. You may upload a screenshot of the success screen.',
        'currency' => 'INR',
        'timezone' => 'Asia/Kolkata',
        'kitchen_lead_hours' => null,
        'order_max_future_days' => 7,
        'order_min_hours_before_delivery' => 4,
        'facebook_url' => '',
        'instagram_url' => '',
        'twitter_url' => '',
        'product_note' => '',
        'message_on_cake_max_length' => '50',
        'checkout_delivery_notice' => 'We deliver only within Rajkot. Enter your pincode to check availability.',
        'checkout_takeaway_notice' => 'Pickup is only available at our store:',
        'checkout_takeaway_address' => null,
        ...self::NOTIFICATION_DEFAULTS,
        ...self::PAYMENT_DEFAULTS,
    ];

    public static function tableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    public static function get(string $key, $default = null)
    {
        if (in_array($key, self::ENCRYPTED_KEYS, true)) {
            return self::getEncrypted($key, $default);
        }

        $all = static::allCached();

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    /**
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        if (! static::tableExists()) {
            return self::DEFAULTS;
        }

        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $rows = static::query()->pluck('value', 'key');

            return array_merge(self::DEFAULTS, $rows->toArray());
        });
    }

    public static function set(string $key, $value): void
    {
        if (! static::tableExists()) {
            return;
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value]
        );
        Cache::forget(self::CACHE_KEY);
    }

    public static function setEncrypted(string $key, ?string $plain): void
    {
        if ($plain === null) {
            static::set($key, null);

            return;
        }

        $trimmed = trim($plain);
        if ($trimmed === '') {
            return;
        }

        static::set($key, Crypt::encryptString($trimmed));
    }

    public static function getEncrypted(string $key, $default = null): ?string
    {
        if (! static::tableExists()) {
            return $default;
        }

        $raw = static::query()->where('key', $key)->value('value');

        if ($raw === null || $raw === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (DecryptException $e) {
            report($e);

            return $default;
        }
    }

    public static function hasEncryptedValue(string $key): bool
    {
        if (! static::tableExists()) {
            return false;
        }

        $raw = static::query()->where('key', $key)->value('value');

        return $raw !== null && $raw !== '';
    }

    public static function maskedEncryptedValue(string $key, int $visibleSuffix = 4): ?string
    {
        if (! self::hasEncryptedValue($key)) {
            return null;
        }

        return SecretMask::mask(self::getEncrypted($key), $visibleSuffix);
    }

    public static function getPusherKey(): ?string
    {
        return self::getEncrypted('pusher_app_key') ?: env('PUSHER_APP_KEY');
    }

    public static function getPusherSecret(): ?string
    {
        return self::getEncrypted('pusher_app_secret') ?: env('PUSHER_APP_SECRET');
    }

    public static function getPusherAppId(): ?string
    {
        return self::getEncrypted('pusher_app_id') ?: env('PUSHER_APP_ID');
    }

    public static function getPusherCluster(): string
    {
        return self::getEncrypted('pusher_app_cluster') ?: env('PUSHER_APP_CLUSTER', 'mt1');
    }

    public static function getWebPushPublicKey(): ?string
    {
        return self::getEncrypted('webpush_public_key') ?: env('VAPID_PUBLIC_KEY');
    }

    public static function getWebPushPrivateKey(): ?string
    {
        return self::getEncrypted('webpush_private_key') ?: env('VAPID_PRIVATE_KEY');
    }

    public static function isNotificationsEnabled(): bool
    {
        return (string) self::get('notifications_enabled', '1') === '1';
    }

    public static function isWebPushEnabled(): bool
    {
        return (string) self::get('notifications_web_push_enabled', '0') === '1'
            && self::getWebPushPublicKey()
            && self::getWebPushPrivateKey();
    }

    public static function isPusherConfigured(): bool
    {
        return filled(self::getPusherKey())
            && filled(self::getPusherSecret())
            && filled(self::getPusherAppId());
    }

    public static function getRazorpayKeyId(): ?string
    {
        return self::getEncrypted('razorpay_key_id') ?: env('RAZORPAY_KEY_ID');
    }

    public static function getRazorpayKeySecret(): ?string
    {
        return self::getEncrypted('razorpay_key_secret') ?: env('RAZORPAY_KEY_SECRET');
    }

    public static function isRazorpayConfigured(): bool
    {
        return filled(self::getRazorpayKeyId()) && filled(self::getRazorpayKeySecret());
    }

    public static function getPaymentGateway(): ?string
    {
        if (! static::tableExists()) {
            return self::PAYMENT_DEFAULTS['payment_gateway'];
        }

        $hasRow = static::query()->where('key', 'payment_gateway')->exists();

        if ($hasRow) {
            $value = static::query()->where('key', 'payment_gateway')->value('value');

            if ($value === null || $value === '') {
                return null;
            }

            return (string) $value;
        }

        return self::PAYMENT_DEFAULTS['payment_gateway'];
    }

    /**
     * @return array{key_id: string, key_secret: string}|null
     */
    public static function razorpayConfig(): ?array
    {
        if (! self::isRazorpayConfigured()) {
            return null;
        }

        return [
            'key_id' => self::getRazorpayKeyId(),
            'key_secret' => self::getRazorpayKeySecret(),
        ];
    }

    /**
     * @return array{key: string, secret: string, app_id: string, cluster: string}|null
     */
    public static function pusherConfig(): ?array
    {
        if (! self::isPusherConfigured()) {
            return null;
        }

        return [
            'key' => self::getPusherKey(),
            'secret' => self::getPusherSecret(),
            'app_id' => self::getPusherAppId(),
            'cluster' => self::getPusherCluster(),
        ];
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
