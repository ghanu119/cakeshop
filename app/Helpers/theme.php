<?php

if (! function_exists('storefront_theme_key')) {
    /**
     * Theme key for the storefront (Admin → Settings).
     * When unset, matches SetActiveTheme middleware default (warm), not config('themes.default').
     */
    function storefront_theme_key(): string
    {
        $available = array_keys(config('themes.available', ['warm' => []]));
        $fromSetting = settings('theme');

        if ($fromSetting && in_array($fromSetting, $available, true)) {
            return $fromSetting;
        }

        return 'warm';
    }
}

if (! function_exists('active_theme')) {
    /**
     * Get the active front-end theme key (from Settings or config).
     * Always returns a key that exists in config('themes.available').
     */
    function active_theme(): string
    {
        return storefront_theme_key();
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

if (! function_exists('uses_better_buns_checkout')) {
    /**
     * Checkout with order type / delivery address (Better Buns + Warm storefront).
     */
    function uses_better_buns_checkout(): bool
    {
        return in_array(storefront_theme_key(), ['better-buns', 'warm'], true);
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
