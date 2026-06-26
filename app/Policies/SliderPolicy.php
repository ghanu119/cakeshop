<?php

namespace App\Policies;

use App\Models\Slider;
use App\Models\User;

class SliderPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('sliders.view');
    }

    public function view(User $user, Slider $slider): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('sliders.view');
    }

    public function update(User $user, Slider $slider): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('sliders.update');
    }
}
