<?php

if (! function_exists('active_theme')) {
    /**
     * Get the active front-end theme key (from Settings or config).
     * Always returns a key that exists in config('themes.available').
     */
    function active_theme(): string
    {
        $available = array_keys(config('themes.available', ['warm' => []]));
        $default = config('themes.default', 'warm');
        $fromSetting = settings('theme');

        $theme = $fromSetting && in_array($fromSetting, $available, true)
            ? $fromSetting
            : (in_array($default, $available, true) ? $default : 'warm');

        return $theme;
    }
}

if (! function_exists('theme')) {
    /**
     * Alias for active_theme() for use in views.
     */
    function theme(): string
    {
        return active_theme();
    }
}

if (! function_exists('themes_available')) {
    /**
     * Get the list of available themes (key => name) for dropdowns etc.
     *
     * @return array<string, string>
     */
    function themes_available(): array
    {
        $themes = config('themes.available', []);
        return array_map(fn (array $t) => $t['name'] ?? $t['description'] ?? '', $themes);
    }
}
