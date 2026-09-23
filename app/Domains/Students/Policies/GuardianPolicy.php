<?php

namespace App\Domains\Students\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Guardian;

class GuardianPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('guardians.view');
    }

    public function view(?User $user, Guardian $guardian): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('guardians.create');
    }

    public function update(?User $user, Guardian $guardian): bool
    {
        return (bool) $user?->hasPermission('guardians.edit');
    }

    public function delete(?User $user, Guardian $guardian): bool
    {
        return (bool) $user?->hasPermission('guardians.delete');
    }
}
