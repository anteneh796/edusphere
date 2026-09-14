<?php

namespace App\Domains\Settings\Policies;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;

class SettingPolicy
{
    public function view(?User $user): bool
    {
        return $user && ($user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Principal->value));
    }

    public function update(?User $user): bool
    {
        return $user && $user->hasRole(RoleName::SuperAdmin->value);
    }
}
