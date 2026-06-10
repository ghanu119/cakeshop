<?php

use App\Models\Setting;
use App\Models\SiteSetting;

if (! function_exists('settings')) {
    /**
     * Get a setting value by key.
     */
    function settings(?string $key = null, $default = null)
    {
        if ($key === null) {
            return Setting::allCached();
        }
        return Setting::get($key, $default);
    }
}

if (! function_exists('header_icon_url')) {
    /**
     * Get the site header icon URL from settings (uploaded in admin). Returns null if not set.
     */
    function header_icon_url(): ?string
    {
        $url = SiteSetting::first()?->getFirstMediaUrl('header_icon');
        return $url ?: null;
    }
}

if (! function_exists('branding_logo_url')) {
    /**
     * Absolute URL for the brand logo, suitable for email clients.
     */
    function branding_logo_url(): ?string
    {
        $url = header_icon_url();

        return $url ? url($url) : null;
    }
}

if (! function_exists('pusher_settings')) {
    /**
     * Resolved Pusher config from encrypted settings (null when not configured).
     *
     * @return array{key: string, secret: string, app_id: string, cluster: string}|null
     */
    function pusher_settings(): ?array
    {
        return Setting::isPusherConfigured() ? Setting::pusherConfig() : null;
    }
}

if (! function_exists('site_display_name')) {
    /**
     * Human-readable site name for emails and UI.
     */
    function site_display_name(): string
    {
        return settings('site_name') ?: config('app.name');
    }
}
