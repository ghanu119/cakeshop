<?php

namespace App\Http\Middleware;

use App\Support\AuthGuards;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStaffFromAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = $request->user(AuthGuards::STAFF);

        if (! $staff || ! $staff->hasAnyRole(['Admin', 'Kitchen'])) {
            return $next($request);
        }

        $customer = $request->user(AuthGuards::CUSTOMER);

        if ($customer && $customer->isCustomer()) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard');
    }
}
