<?php

namespace App\Domains\Accounts\Policies;

use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;

class UserPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Principal->value));
    }

    public function view(?User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Principal->value));
    }

    public function update(?User $user, User $record): bool
    {
        if (! $user) {
            return false;
        }

        // A user cannot edit themselves into confusion; super admins always allowed.
        if ($user->getKey() === $record->getKey()) {
            return $user->hasRole(RoleName::SuperAdmin->value);
        }

        return $user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Principal->value);
    }

    public function delete(?User $user, User $record): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->getKey() === $record->getKey()) {
            return false;
        }

        if ($record->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return $user->hasRole(RoleName::SuperAdmin->value);
    }
}
