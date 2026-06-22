<?php

namespace App\Http\Middleware;

use App\Support\AuthGuards;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerGuardAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard(AuthGuards::CUSTOMER)->user();

        if (! $user || ! $user->isCustomer()) {
            Auth::guard(AuthGuards::CUSTOMER)->logout();

            return redirect()->guest(route('home', ['auth' => 1]));
        }

        Auth::shouldUse(AuthGuards::CUSTOMER);

        return $next($request);
    }
}
