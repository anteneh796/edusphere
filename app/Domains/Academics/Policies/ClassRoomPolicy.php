<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;

class ClassRoomPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
        ]));
    }

    public function view(?User $user, ClassRoom $classRoom): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]));
    }

    public function update(?User $user, ClassRoom $classRoom): bool
    {
        return $this->create($user);
    }

    public function delete(?User $user, ClassRoom $classRoom): bool
    {
        if (! $this->create($user)) {
            return false;
        }

        return ! $classRoom->students()->withTrashed()->exists() && ! $classRoom->assignments()->exists();
    }
}
