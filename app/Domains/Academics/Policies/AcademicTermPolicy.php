<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\AcademicTerm;
use App\Domains\Accounts\Models\User;

class AcademicTermPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, AcademicTerm $academicTerm): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, AcademicTerm $academicTerm): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, AcademicTerm $academicTerm): bool
    {
        return (bool) $user?->hasPermission('academics.delete');
    }
}
