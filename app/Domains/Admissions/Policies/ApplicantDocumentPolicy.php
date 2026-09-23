<?php

namespace App\Domains\Admissions\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Admissions\Models\ApplicantDocument;

class ApplicantDocumentPolicy
{
    public function create(?User $user, ApplicantDocument $document): bool
    {
        return (bool) $user?->hasPermission('admissions.create');
    }

    public function verify(?User $user, ApplicantDocument $document): bool
    {
        return (bool) $user?->hasPermission('admissions.verify');
    }

    public function delete(?User $user, ApplicantDocument $document): bool
    {
        if (! $user?->hasPermission('admissions.delete')) {
            return false;
        }

        return $document->status === 'pending';
    }
}
