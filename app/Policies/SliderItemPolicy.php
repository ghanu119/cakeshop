<?php

namespace App\Policies;

use App\Models\SliderItem;
use App\Models\User;

class SliderItemPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('slider_items.view') || $user->can('sliders.view');
    }

    public function view(User $user, SliderItem $sliderItem): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('slider_items.create');
    }

    public function update(User $user, SliderItem $sliderItem): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('slider_items.update');
    }

    public function delete(User $user, SliderItem $sliderItem): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->can('slider_items.delete');
    }
}
