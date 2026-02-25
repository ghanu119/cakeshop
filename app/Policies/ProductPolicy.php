<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('products.delete');
    }
}
