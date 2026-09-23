<?php

namespace App\Domains\Admissions\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\AdmissionApplication;
use App\Support\Enums\AdmissionStatus;
use App\Support\Enums\RoleName;

class AdmissionApplicationPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('admissions.view');
    }

    public function view(?User $user, AdmissionApplication $application): bool
    {
        return $this->viewAny($user);
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('admissions.create');
    }

    public function update(?User $user, AdmissionApplication $application): bool
    {
        if (! $user?->hasPermission('admissions.edit')) {
            return false;
        }

        if ($application->status === AdmissionStatus::Enrolled->value) {
            return false;
        }

        if ($user->hasRole(RoleName::Reception->value)) {
            return $application->created_by === $user->getKey()
                && in_array($application->status, [
                    AdmissionStatus::Inquiry->value,
                    AdmissionStatus::Draft->value,
                    AdmissionStatus::Submitted->value,
                ], true);
        }

        return true;
    }

    public function delete(?User $user, AdmissionApplication $application): bool
    {
        if (! $user?->hasPermission('admissions.delete')) {
            return false;
        }

        return $application->status !== AdmissionStatus::Enrolled->value;
    }

    public function submit(?User $user, AdmissionApplication $application): bool
    {
        if (! $user?->hasPermission('admissions.edit')) {
            return false;
        }

        return $this->update($user, $application);
    }

    public function review(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.edit');
    }

    public function scheduleAssessment(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.create');
    }

    public function recordAssessment(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.create');
    }

    public function submitForApproval(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.edit');
    }

    public function decide(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.approve');
    }

    public function waitlist(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.approve');
    }

    public function promote(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.enroll');
    }

    public function enroll(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.enroll');
    }

    public function withdraw(?User $user, AdmissionApplication $application): bool
    {
        return (bool) $user?->hasPermission('admissions.edit');
    }
}
