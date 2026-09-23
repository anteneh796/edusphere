<?php

namespace App\Domains\Admissions\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\GradeCapacity;

class GradeCapacityPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('admissions.capacity');
    }

    public function view(?User $user, GradeCapacity $capacity): bool
    {
        return $this->viewAny($user);
    }

    public function update(?User $user): bool
    {
        return (bool) $user?->hasPermission('admissions.capacity');
    }
}
