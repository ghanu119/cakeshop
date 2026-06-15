<?php

namespace App\Services;

use App\Models\AccountDeletionLog;
use App\Models\CustomerAccountEvent;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerDeletionService
{
    public function __construct(
        private CustomerContext $customerContext,
        private Request $request
    ) {}

    public function softDeleteCustomer(User $customer, string $reason): void
    {
        if (! $customer->isCustomer()) {
            return;
        }

        if ($this->customerContext->isImpersonating()
            && (int) $this->request->session()->get(CustomerContext::SESSION_IMPERSONATED_CUSTOMER_ID) === $customer->id) {
            $this->customerContext->stopImpersonation();
        }

        if (Auth::id() === $customer->id) {
            Auth::logout();
            $this->request->session()->invalidate();
            $this->request->session()->regenerateToken();
        }

        $customer->deletion_reason = $reason;
        $customer->deletion_requested_at = now();
        $customer->delete();

        CustomerAccountEvent::create([
            'user_id' => $customer->id,
            'event' => 'account_soft_deleted',
            'email' => $customer->email,
            'ip_address' => $this->request->ip(),
            'meta' => ['reason' => $reason],
            'created_at' => now(),
        ]);
    }

    public function purgeExpiredCustomers(): int
    {
        $retentionDays = config('privacy.customer_retention_days', 90);
        $cutoff = now()->subDays($retentionDays);

        $customers = User::onlyTrashed()
            ->customers()
            ->where('deleted_at', '<', $cutoff)
            ->get();

        $purged = 0;

        foreach ($customers as $customer) {
            DB::transaction(function () use ($customer, &$purged) {
                $unlinked = Order::withTrashed()
                    ->where('user_id', $customer->id)
                    ->update(['user_id' => null]);

                AccountDeletionLog::create([
                    'user_id' => $customer->id,
                    'purged_at' => now(),
                    'orders_unlinked_count' => $unlinked,
                    'created_at' => now(),
                ]);

                $customer->purged_at = now();
                $customer->saveQuietly();
                $customer->forceDelete();
                $purged++;
            });
        }

        return $purged;
    }
}
