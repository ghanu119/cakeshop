<?php

namespace App\Services;

use App\Models\ImpersonationLog;
use App\Models\User;
use App\Services\CustomerAuthService;
use App\Support\AuthGuards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerContext
{
    public const SESSION_IMPERSONATOR_ID = 'impersonator_id';

    public const SESSION_IMPERSONATED_CUSTOMER_ID = 'impersonated_customer_id';

    public function __construct(
        private Request $request
    ) {}

    public function effectiveCustomer(): ?User
    {
        if (! $this->request->hasSession()) {
            return $this->customerFromGuard();
        }

        if ($customerId = $this->request->session()->get(self::SESSION_IMPERSONATED_CUSTOMER_ID)) {
            $customer = User::customers()->find($customerId);

            if ($customer && $this->impersonatorMatchesSession()) {
                return $customer;
            }

            if ($customerId) {
                $this->clearImpersonation();
            }
        }

        return $this->customerFromGuard();
    }

    public function isImpersonating(): bool
    {
        if (! $this->request->hasSession()) {
            return false;
        }

        return $this->request->session()->has(self::SESSION_IMPERSONATED_CUSTOMER_ID)
            && $this->impersonatorMatchesSession();
    }

    public function impersonator(): ?User
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        return User::find($this->request->session()->get(self::SESSION_IMPERSONATOR_ID));
    }

    public function startImpersonation(User $admin, User $customer): void
    {
        Auth::guard(AuthGuards::CUSTOMER)->logout();
        $this->request->session()->forget(CustomerAuthService::SESSION_VERIFIED_EMAIL);

        $this->request->session()->put(self::SESSION_IMPERSONATOR_ID, $admin->id);
        $this->request->session()->put(self::SESSION_IMPERSONATED_CUSTOMER_ID, $customer->id);

        ImpersonationLog::create([
            'admin_user_id' => $admin->id,
            'customer_user_id' => $customer->id,
            'action' => ImpersonationLog::ACTION_START,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function stopImpersonation(): ?int
    {
        if (! $this->isImpersonating()) {
            $this->clearImpersonation();

            return null;
        }

        $customerId = (int) $this->request->session()->get(self::SESSION_IMPERSONATED_CUSTOMER_ID);
        $adminId = (int) $this->request->session()->get(self::SESSION_IMPERSONATOR_ID);

        ImpersonationLog::create([
            'admin_user_id' => $adminId,
            'customer_user_id' => $customerId,
            'action' => ImpersonationLog::ACTION_STOP,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => now(),
        ]);

        $this->clearImpersonation();

        return $customerId;
    }

    public function logOrderPlaced(User $admin, User $customer, int $orderId): void
    {
        ImpersonationLog::create([
            'admin_user_id' => $admin->id,
            'customer_user_id' => $customer->id,
            'action' => ImpersonationLog::ACTION_ORDER_PLACED,
            'order_id' => $orderId,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function clearImpersonation(): void
    {
        $this->request->session()->forget([
            self::SESSION_IMPERSONATOR_ID,
            self::SESSION_IMPERSONATED_CUSTOMER_ID,
        ]);
    }

    private function customerFromGuard(): ?User
    {
        $user = Auth::guard(AuthGuards::CUSTOMER)->user();

        if ($user && $user->isCustomer()) {
            return $user;
        }

        return null;
    }

    private function impersonatorMatchesSession(): bool
    {
        if (! $this->request->hasSession()) {
            return false;
        }

        $admin = Auth::guard(AuthGuards::STAFF)->user();

        if (! $admin || ! $admin->hasRole('Admin', AuthGuards::STAFF)) {
            return false;
        }

        return (int) $this->request->session()->get(self::SESSION_IMPERSONATOR_ID) === $admin->id;
    }
}
