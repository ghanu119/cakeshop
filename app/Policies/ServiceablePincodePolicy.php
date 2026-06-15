<?php

namespace App\Policies;

use App\Models\ServiceablePincode;
use App\Models\User;

class ServiceablePincodePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('settings.manage');
    }

    public function view(User $user, ServiceablePincode $pincode): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('settings.manage');
    }

    public function update(User $user, ServiceablePincode $pincode): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ServiceablePincode $pincode): bool
    {
        return $this->create($user);
    }
}
