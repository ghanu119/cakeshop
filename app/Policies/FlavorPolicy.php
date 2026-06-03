<?php

namespace App\Policies;

use App\Models\Flavor;
use App\Models\User;

class FlavorPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('flavors.view');
    }

    public function view(User $user, Flavor $flavor): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('flavors.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('flavors.create');
    }

    public function update(User $user, Flavor $flavor): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('flavors.update');
    }

    public function delete(User $user, Flavor $flavor): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('flavors.delete');
    }
}
