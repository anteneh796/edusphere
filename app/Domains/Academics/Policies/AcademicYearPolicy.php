<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\AcademicYear;
use App\Domains\Accounts\Models\User;

class AcademicYearPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, AcademicYear $academicYear): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, AcademicYear $academicYear): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, AcademicYear $academicYear): bool
    {
        if (! $user?->hasPermission('academics.delete')) {
            return false;
        }

        return ! $academicYear->classRooms()->exists();
    }
}
