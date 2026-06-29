<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('coupons.view');
    }

    public function view(User $user, Coupon $coupon): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('coupons.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('coupons.create');
    }

    public function update(User $user, Coupon $coupon): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('coupons.update');
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('coupons.delete');
    }
}
