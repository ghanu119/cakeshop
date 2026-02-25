<?php

namespace App\Http\Middleware;

use Hexadog\ThemesManager\Http\Middleware\ThemeLoader as BaseThemeLoader;

class SetActiveTheme extends BaseThemeLoader
{
    /**
     * Set the active theme from Admin → Settings (warm / lumiere)
     * so each theme uses its own layout, CSS, and assets without overlap.
     */
    public function handle($request, \Closure $next, ?string $theme = null)
    {
        if ($theme !== null) {
            return parent::handle($request, $next, $theme);
        }

        if ($request->is('admin*')) {
            return $next($request);
        }

        $fromSetting = settings('theme');
        $theme = $fromSetting === 'lumiere' ? 'cakeshop/lumiere' : 'cakeshop/warm';
        return parent::handle($request, $next, $theme);
    }
}
