<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsService
{
    public function updateFromRequest(Request $request): void
    {
        $keys = array_keys(Setting::DEFAULTS);

        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value === '' || $value === null) {
                $value = array_key_exists($key, Setting::DEFAULTS) ? Setting::DEFAULTS[$key] : null;
            }
            Setting::set($key, $value);
        }

        Setting::flushCache();
    }
}
