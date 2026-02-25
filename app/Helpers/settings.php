<?php

use App\Models\Setting;
use App\Models\SiteSetting;

if (! function_exists('settings')) {
    /**
     * Get a setting value by key.
     */
    function settings(string $key = null, $default = null)
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
