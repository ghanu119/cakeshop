<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->can('settings.manage');
    }
}
