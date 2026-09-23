<?php

namespace App\Domains\Notifications\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Notifications\Models\Notification;

class NotificationPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('notifications.view');
    }

    public function view(?User $user, Notification $notification): bool
    {
        return (bool) $user?->hasPermission('notifications.view')
            && $notification->user_id === $user?->getKey();
    }

    public function update(?User $user, Notification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
