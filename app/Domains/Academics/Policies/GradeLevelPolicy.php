<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\GradeLevel;
use App\Domains\Accounts\Models\User;

class GradeLevelPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, GradeLevel $gradeLevel): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, GradeLevel $gradeLevel): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, GradeLevel $gradeLevel): bool
    {
        if (! $user?->hasPermission('academics.delete')) {
            return false;
        }

        return ! $gradeLevel->classRooms()->exists() && ! $gradeLevel->students()->exists();
    }
}
