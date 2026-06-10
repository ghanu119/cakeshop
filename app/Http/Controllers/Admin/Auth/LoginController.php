<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check() && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Kitchen'))) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login. Only Admin and Kitchen roles may log in here.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = ! empty($credentials['remember']);
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if (! $user->hasRole('Admin') && ! $user->hasRole('Kitchen')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => [__('You do not have access to the admin area.')],
            ]);
        }

        if (Setting::isWebPushEnabled()) {
            $request->session()->put('prompt_staff_push', true);
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
