<?php

namespace App\Http\Middleware;

use Hexadog\ThemesManager\Facades\ThemesManager;
use Hexadog\ThemesManager\Http\Middleware\ThemeLoader as BaseThemeLoader;

class SetActiveTheme extends BaseThemeLoader
{
    /**
     * Set the active theme from Admin → Settings
     * so each theme uses its own layout, CSS, and assets without overlap.
     *
     * Note: we deliberately do NOT delegate to the parent ThemeLoader here.
     * Its handle() skips ThemesManager::set() whenever $request->expectsJson()
     * is true — which includes our own storefront ajax/autoload endpoints
     * (jQuery's default Accept header makes expectsJson() true). That would
     * leave the theme's view paths unregistered and silently fall back to
     * the base resources/views for any ajax-rendered partial.
     */
    public function handle($request, \Closure $next, ?string $theme = null)
    {
        if (app()->runningInConsole()) {
            return $next($request);
        }

        if ($theme === null) {
            if ($request->is('admin*')) {
                return $next($request);
            }

            $fromSetting = settings('theme');
            $themeMap = [
                'lumiere' => 'cakeshop/lumiere',
                'better-buns' => 'cakeshop/better-buns',
                'warm' => 'cakeshop/warm',
            ];
            $theme = $themeMap[$fromSetting] ?? 'cakeshop/warm';
        }

        ThemesManager::set($theme);

        return $next($request);
    }
}
