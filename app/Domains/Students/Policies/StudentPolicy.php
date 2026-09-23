<?php

namespace App\Domains\Students\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Students\Models\Student;

class StudentPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('students.view');
    }

    public function view(?User $user, Student $student): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('students.create');
    }

    public function update(?User $user, Student $student): bool
    {
        return (bool) $user?->hasPermission('students.edit');
    }

    public function delete(?User $user, Student $student): bool
    {
        return (bool) $user?->hasPermission('students.delete');
    }

    public function export(?User $user): bool
    {
        return (bool) $user?->hasPermission('students.export');
    }

    public function viewMedical(?User $user, Student $student): bool
    {
        return (bool) $user?->hasPermission('students.medical');
    }

    public function viewDocuments(?User $user, Student $student): bool
    {
        return (bool) $user?->hasPermission('students.documents');
    }

    public function transfer(?User $user, Student $student): bool
    {
        return (bool) $user?->hasPermission('students.transfer');
    }

    public function promote(?User $user): bool
    {
        return (bool) $user?->hasPermission('students.promote');
    }
}
