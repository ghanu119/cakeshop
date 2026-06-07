<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'app_settings';

    /**
     * Whether the settings table exists (e.g. false during migrate:fresh before migrations run).
     */
    public static function tableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    public const DEFAULTS = [
        'site_name' => 'Cake Shop',
        'theme' => null, // null = use config/themes.php default
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
    ];

    public static function get(string $key, $default = null)
    {
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

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
