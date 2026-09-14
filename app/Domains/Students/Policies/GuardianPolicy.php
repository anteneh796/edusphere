<?php

namespace App\Domains\Students\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Guardian;
use App\Support\Enums\RoleName;

class GuardianPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
        ]));
    }

    public function view(?User $user, Guardian $guardian): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
        ]));
    }

    public function update(?User $user, Guardian $guardian): bool
    {
        return $this->create($user);
    }

    public function delete(?User $user, Guardian $guardian): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]));
    }
}
