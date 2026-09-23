<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\ClassRoom;
use App\Domains\Accounts\Models\User;

class ClassRoomPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, ClassRoom $classRoom): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, ClassRoom $classRoom): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, ClassRoom $classRoom): bool
    {
        if (! $user?->hasPermission('academics.delete')) {
            return false;
        }

        return ! $classRoom->students()->withTrashed()->exists() && ! $classRoom->assignments()->exists();
    }
}
