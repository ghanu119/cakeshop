<?php

namespace App\Policies;

use App\Models\Feature;
use App\Models\User;

class FeaturePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('features.view');
    }

    public function view(User $user, Feature $feature): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('features.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('features.create');
    }

    public function update(User $user, Feature $feature): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('features.update');
    }

    public function delete(User $user, Feature $feature): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('features.delete');
    }
}
