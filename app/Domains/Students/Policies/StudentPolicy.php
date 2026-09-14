<?php

namespace App\Domains\Students\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;
use App\Support\Enums\RoleName;

class StudentPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
            RoleName::Accountant->value,
        ]));
    }

    public function view(?User $user, Student $student): bool
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

    public function update(?User $user, Student $student): bool
    {
        return $this->create($user);
    }

    public function delete(?User $user, Student $student): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]));
    }

    public function export(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
        ]));
    }
}
