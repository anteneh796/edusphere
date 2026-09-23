<?php

namespace App\Domains\Settings\Policies;

use App\Domains\Accounts\Models\User;

class SettingPolicy
{
    public function view(?User $user): bool
    {
        return (bool) $user?->hasPermission('settings.view');
    }

    public function update(?User $user): bool
    {
        return (bool) $user?->hasPermission('settings.edit');
    }
}
