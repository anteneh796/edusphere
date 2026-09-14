<?php

namespace App\Domains\Attendance\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Support\Enums\RoleName;

class AttendancePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
            RoleName::Registrar->value,
            RoleName::Teacher->value,
        ]));
    }

    public function view(?User $user, AttendanceSession $session): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(?User $user, AttendanceSession $session): bool
    {
        return $this->viewAny($user);
    }

    public function delete(?User $user, AttendanceSession $session): bool
    {
        return $user && ($user->hasRole([
            RoleName::SuperAdmin->value,
            RoleName::Principal->value,
        ]));
    }

    public function close(?User $user, AttendanceSession $session): bool
    {
        return $this->update($user, $session);
    }
}
