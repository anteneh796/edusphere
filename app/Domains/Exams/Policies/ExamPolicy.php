<?php

namespace App\Domains\Exams\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;
use App\Support\Enums\RoleName;

class ExamPolicy
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

    public function view(?User $user, Exam $exam): bool
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

    public function update(?User $user, Exam $exam): bool
    {
        return $this->create($user);
    }

    public function managePapers(?User $user, Exam $exam): bool
    {
        return $this->create($user);
    }

    public function enterResults(?User $user, Exam $exam): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
        ]));
    }

    public function delete(?User $user, Exam $exam): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]));
    }
}
