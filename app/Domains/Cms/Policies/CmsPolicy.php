<?php

namespace App\Domains\Cms\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Cms\Models\GalleryItem;
use App\Domains\Cms\Models\NewsItem;
use App\Domains\Cms\Models\Page;
use App\Support\Enums\RoleName;

class CmsPolicy
{
    private const MANAGE_ROLES = [
        RoleName::SuperAdmin->value,
        RoleName::Principal->value,
    ];

    private function canManage(?User $user): bool
    {
        return (bool) $user?->hasRole(self::MANAGE_ROLES);
    }

    public function before(?User $user, string $ability): ?bool
    {
        return $this->canManage($user) ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(?User $user, mixed $model = null): bool
    {
        return $this->canManage($user);
    }

    public function create(?User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(?User $user, mixed $model = null): bool
    {
        return $this->canManage($user);
    }

    public function delete(?User $user, mixed $model = null): bool
    {
        return $this->canManage($user);
    }
}