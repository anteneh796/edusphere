<?php

namespace App\Domains\Cms\Policies;

use App\Domains\Accounts\Models\User;

class CmsPolicy
{
    public function before(?User $user, string $ability): ?bool
    {
        if ($user && $user->hasAnyPermission(['cms.view', 'cms.create', 'cms.edit', 'cms.delete'])) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('cms.view');
    }

    public function view(?User $user, mixed $model = null): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('cms.create');
    }

    public function update(?User $user, mixed $model = null): bool
    {
        return (bool) $user?->hasPermission('cms.edit');
    }

    public function delete(?User $user, mixed $model = null): bool
    {
        return (bool) $user?->hasPermission('cms.delete');
    }
}
