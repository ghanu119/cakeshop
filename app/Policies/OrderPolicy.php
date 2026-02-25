<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('orders.view');
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('orders.update');
    }
}
