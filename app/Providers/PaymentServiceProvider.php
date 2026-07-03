<?php

namespace App\Providers;

use App\Services\Payments\PaymentManager;
use App\Services\Payments\PaymentOrchestrator;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentSettingsResolver;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentSettingsResolver::class);
        $this->app->singleton(PaymentManager::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(PaymentOrchestrator::class);
        $this->app->singleton(PaymentErrorMapper::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            $this->app->make(PaymentSettingsResolver::class)->applyGatewayConfig();
        }
    }
}
