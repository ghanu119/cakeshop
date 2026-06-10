<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StaffPushSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __construct(
        private StaffPushSubscriptionService $staffPushSubscriptionService
    ) {}

    /**
     * Log the user out and redirect to home.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->staffPushSubscriptionService->purgeForUser($user);
        }

        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
