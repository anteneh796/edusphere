<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\Subject;
use App\Domains\Accounts\Models\User;

class SubjectPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, Subject $subject): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, Subject $subject): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, Subject $subject): bool
    {
        return (bool) $user?->hasPermission('academics.delete') && ! $subject->assignments()->exists();
    }
}
