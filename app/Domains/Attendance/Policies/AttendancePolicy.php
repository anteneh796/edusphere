<?php

namespace App\Domains\Attendance\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceSession;

class AttendancePolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('attendance.view');
    }

    public function view(?User $user, AttendanceSession $session): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('attendance.create');
    }

    public function update(?User $user, AttendanceSession $session): bool
    {
        return (bool) $user?->hasPermission('attendance.edit');
    }

    public function delete(?User $user, AttendanceSession $session): bool
    {
        return (bool) $user?->hasPermission('attendance.delete');
    }

    public function close(?User $user, AttendanceSession $session): bool
    {
        return $this->update($user, $session);
    }
}
