<?php

namespace App\Http\Middleware;

use App\Services\PusherSettingsResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyBroadcastingConfig
{
    public function __construct(
        private PusherSettingsResolver $pusherSettingsResolver
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->pusherSettingsResolver->applyBroadcastingConfig();

        return $next($request);
    }
}
