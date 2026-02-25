<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('categories.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('categories.update');
    }

    public function delete(User $user, Category $category): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('categories.delete');
    }
}
