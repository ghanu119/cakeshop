<?php

namespace App\Providers;

use App\Services\PusherSettingsResolver;
use App\View\Composers\AdminNotificationComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Strong password rule (plan: never compromise on security)
        Password::defaults(function () {
            return Password::min(8)->mixedCase()->numbers()->symbols();
        });

        // Share active theme with all views (for data-theme and @themeIs)
        View::share('activeTheme', active_theme());

        // Blade directive: @themeIs('warm') ... @endthemeIs
        Blade::if('themeIs', function (string $theme) {
            return active_theme() === $theme;
        });

        View::composer([
            'layouts.admin',
            'dashboard',
            'dashboard.*',
            'admin.*',
            'kitchen.*',
        ], AdminNotificationComposer::class);

        $appUrl = (string) config('app.url');
        if (preg_match('/\.(test|localhost)(:\d+)?$/i', parse_url($appUrl, PHP_URL_HOST) ?? '')) {
            URL::forceScheme('https');
        }

        if (! $this->app->runningInConsole()) {
            $this->app->make(PusherSettingsResolver::class)->applyBroadcastingConfig();
        }
    }
}
