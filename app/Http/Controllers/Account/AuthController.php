<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\AuthGuards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function redirectToAuthModal(Request $request): RedirectResponse
    {
        if ($request->filled('intended')) {
            session(['url.intended' => $request->query('intended')]);
        }

        $target = url()->previous();
        $parsed = parse_url($target);
        $path = $parsed['path'] ?? '/';

        if ($path === '/account/login' || $path === '/account/verify-otp' || $path === '/account/register') {
            $target = route('home');
        }

        $separator = str_contains($target, '?') ? '&' : '?';

        return redirect($target.$separator.'auth=1');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard(AuthGuards::CUSTOMER)->logout();

        return redirect()->route('home');
    }
}
