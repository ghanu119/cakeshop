<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin') || $user->can('customers.view');
    }

    public function view(User $user, User $customer): bool
    {
        if (! $customer->isCustomer()) {
            return false;
        }

        return $user->hasRole('Admin') || $user->can('customers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin') || $user->can('customers.create');
    }

    public function update(User $user, User $customer): bool
    {
        if (! $customer->isCustomer()) {
            return false;
        }

        return $user->hasRole('Admin') || $user->can('customers.update');
    }

    public function delete(User $user, User $customer): bool
    {
        if (! $customer->isCustomer() || $customer->trashed()) {
            return false;
        }

        return $user->hasRole('Admin') || $user->can('customers.delete');
    }

    public function impersonate(User $user, User $customer): bool
    {
        if (! $customer->isCustomer() || $customer->trashed()) {
            return false;
        }

        return $user->hasRole('Admin') && $user->can('customers.impersonate');
    }
}
