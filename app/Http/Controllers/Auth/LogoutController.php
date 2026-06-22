<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StaffPushSubscriptionService;
use App\Support\AuthGuards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __construct(
        private StaffPushSubscriptionService $staffPushSubscriptionService
    ) {}

    /**
     * Log staff out of the admin session without touching storefront customer login.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::guard(AuthGuards::STAFF)->user();

        if ($user instanceof User) {
            $this->staffPushSubscriptionService->purgeForUser($user);
        }

        Auth::guard(AuthGuards::STAFF)->logout();

        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
