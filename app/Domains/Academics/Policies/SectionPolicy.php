<?php

namespace App\Domains\Academics\Policies;

use App\Domains\Academics\Models\Section;
use App\Domains\Accounts\Models\User;

class SectionPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.view');
    }

    public function view(?User $user, Section $section): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('academics.create');
    }

    public function update(?User $user, Section $section): bool
    {
        return (bool) $user?->hasPermission('academics.edit');
    }

    public function delete(?User $user, Section $section): bool
    {
        return (bool) $user?->hasPermission('academics.delete');
    }
}
