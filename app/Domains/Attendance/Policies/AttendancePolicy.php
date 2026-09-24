<?php

namespace App\Domains\Attendance\Policies;

use App\Domains\Academics\Models\ClassSubject;
use App\Domains\Accounts\Models\User;
use App\Domains\Attendance\Models\AttendanceSession;
use App\Support\Enums\RoleName;

class AttendancePolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('attendance.view');
    }

    public function view(?User $user, AttendanceSession $session): bool
    {
        if (! $user?->hasPermission('attendance.view')) {
            return false;
        }

        if (! $user->hasRole(RoleName::Teacher->value)) {
            return true;
        }

        return $this->teacherOwnsClass($user, $session);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('attendance.create');
    }

    public function update(?User $user, AttendanceSession $session): bool
    {
        if (! $user?->hasPermission('attendance.edit')) {
            return false;
        }

        if (! $user->hasRole(RoleName::Teacher->value)) {
            return true;
        }

        return $this->teacherOwnsClass($user, $session);
    }

    public function delete(?User $user, AttendanceSession $session): bool
    {
        return (bool) $user?->hasPermission('attendance.delete')
            && ! $user->hasRole(RoleName::Teacher->value);
    }

    public function close(?User $user, AttendanceSession $session): bool
    {
        return $this->update($user, $session);
    }

    private function teacherOwnsClass(User $user, AttendanceSession $session): bool
    {
        return ClassSubject::query()
            ->where('teacher_id', $user->getKey())
            ->where('class_room_id', $session->class_room_id)
            ->exists();
    }
}
