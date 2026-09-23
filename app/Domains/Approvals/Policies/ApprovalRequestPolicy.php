<?php

namespace App\Domains\Approvals\Policies;

use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;

class ApprovalRequestPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->hasPermission('approvals.view');
    }

    public function view(?User $user, ApprovalRequest $request): bool
    {
        if (! $user?->hasPermission('approvals.view')) {
            return false;
        }

        if ($user->hasPermission('approvals.approve')) {
            return true;
        }

        return $request->requested_by_id === $user->getKey();
    }

    public function create(?User $user): bool
    {
        return (bool) $user?->hasPermission('approvals.create');
    }

    public function approve(?User $user, ApprovalRequest $request): bool
    {
        return (bool) $user?->hasPermission('approvals.approve');
    }

    public function deny(?User $user, ApprovalRequest $request): bool
    {
        return $this->approve($user, $request);
    }
}
