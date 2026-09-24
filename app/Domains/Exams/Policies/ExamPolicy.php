<?php

namespace App\Domains\Exams\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Exams\Models\Exam;

class ExamPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('exams.view');
    }

    public function view(?User $user, Exam $exam): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('exams.create');
    }

    public function update(?User $user, Exam $exam): bool
    {
        return (bool) $user?->hasPermission('exams.edit');
    }

    public function managePapers(?User $user, Exam $exam): bool
    {
        return (bool) $user?->hasPermission('exams.edit');
    }

    public function enterResults(?User $user, Exam $exam): bool
    {
        if (! $user) {
            return false;
        }

        // Teachers may enter results only through the teacher/class assignment
        // for the paper. Administrative users need the normal exam edit access.
        if ($user->hasRole('teacher')) {
            return true;
        }

        return $user->hasPermission('exams.edit');
    }

    public function delete(?User $user, Exam $exam): bool
    {
        return (bool) $user?->hasPermission('exams.delete');
    }
}
