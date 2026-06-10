<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

class StaffPushSubscriptionService
{
    public function assertStaffRecipient(Authenticatable $user): void
    {
        if (! $user instanceof User) {
            throw new AuthorizationException(__('You don\'t have permission to do that.'));
        }

        if (! $this->isEligibleStaff($user)) {
            throw new AuthorizationException(__('Only admin and kitchen staff can receive these notifications.'));
        }
    }

    public function isEligibleStaff(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        return $user->hasAnyRole(['Admin', 'Kitchen']);
    }

    public function purgeForUser(User $user): void
    {
        $user->pushSubscriptions()->delete();
    }
}
