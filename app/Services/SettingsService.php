<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsService
{
    public function __construct(
        private WebPushVapidService $webPushVapidService
    ) {}

    public function updateFromRequest(Request $request): void
    {
        $plainKeys = array_diff(array_keys(Setting::DEFAULTS), Setting::ENCRYPTED_KEYS);

        foreach ($plainKeys as $key) {
            if (! $request->has($key)) {
                continue;
            }

            $value = $request->input($key);

            if ($key === 'notifications_enabled' || $key === 'notifications_web_push_enabled') {
                $enabled = $request->boolean($key) ? '1' : '0';
                Setting::set($key, $enabled);

                if ($key === 'notifications_enabled' && $enabled === '1') {
                    Setting::set('notifications_web_push_enabled', '1');
                    $this->webPushVapidService->ensureKeysProvisioned();
                }

                if ($key === 'notifications_web_push_enabled' && $enabled === '1') {
                    $this->webPushVapidService->ensureKeysProvisioned();
                }

                continue;
            }

            if ($key === 'payment_gateway') {
                Setting::set($key, filled($value) ? $value : null);

                continue;
            }

            if ($value === '' || $value === null) {
                $value = array_key_exists($key, Setting::DEFAULTS) ? Setting::DEFAULTS[$key] : null;
            }

            Setting::set($key, $value);
        }

        if ($request->boolean('clear_razorpay_credentials')) {
            Setting::setEncrypted('razorpay_key_id', null);
            Setting::setEncrypted('razorpay_key_secret', null);
        }

        if ($request->boolean('clear_pusher_credentials')) {
            foreach (['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster'] as $key) {
                Setting::setEncrypted($key, null);
            }
        }

        foreach (Setting::ENCRYPTED_KEYS as $key) {
            if ($request->filled($key)) {
                Setting::setEncrypted($key, $request->input($key));
            }
        }

        Setting::flushCache();
    }
}
