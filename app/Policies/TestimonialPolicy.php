<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('testimonials.view');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('testimonials.view');
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('testimonials.create');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('testimonials.update');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('testimonials.delete');
    }
}
