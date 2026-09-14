<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;
use App\Support\Enums\RoleName;

class SubjectPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
        ]));
    }

    public function view(?User $user, Subject $subject): bool
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

    public function update(?User $user, Subject $subject): bool
    {
        return $this->create($user);
    }

    public function delete(?User $user, Subject $subject): bool
    {
        return $this->create($user) && ! $subject->assignments()->exists();
    }
}
