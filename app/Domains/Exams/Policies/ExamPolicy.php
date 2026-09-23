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
        return $this->viewAny($user);
    }

    public function delete(?User $user, Exam $exam): bool
    {
        return (bool) $user?->hasPermission('exams.delete');
    }
}
