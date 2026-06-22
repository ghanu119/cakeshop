<?php

namespace App\Http\Middleware;

use App\Support\AuthGuards;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStaffFromAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(AuthGuards::STAFF);

        if ($user && $user->hasAnyRole(['Admin', 'Kitchen'])) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
