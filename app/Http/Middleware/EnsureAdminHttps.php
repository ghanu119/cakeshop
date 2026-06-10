<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing') || $request->secure()) {
            return $next($request);
        }

        $host = $request->getHost();

        $shouldForceHttps = app()->environment('production')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');

        if ($shouldForceHttps) {
            $uri = $request->getRequestUri();

            return redirect()->away('https://'.$host.$uri, 301);
        }

        return $next($request);
    }
}
