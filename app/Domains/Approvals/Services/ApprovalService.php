<?php

namespace App\Domains\Approvals\Services;

use App\Domains\Accounts\Models\User;
use App\Domains\Approvals\Models\ApprovalRequest;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Students\Models\Student;
use App\Support\ActivityLogger;
use App\Support\Enums\ApprovalStatus;
use App\Support\Enums\ApprovalType;
use App\Support\Enums\StudentStatus;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /**
     * Open a new approval request and notify its reviewers.
     */
    public function submit(
        ApprovalType $type,
        User $requester,
        ?string $reason = null,
        ?string $subjectType = null,
        ?string $subjectId = null,
        array $data = [],
    ): ApprovalRequest {
        $request = ApprovalRequest::create([
            'type' => $type->value,
            'status' => ApprovalStatus::Pending->value,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'requested_by_id' => $requester->getKey(),
            'reason' => $reason,
            'data' => $data,
            'submitted_at' => now(),
        ]);

        ActivityLogger::log('submitted '.$type->label().' request', 'approvals', $request->getKey(), [
            'type' => $type->value,
        ], $requester->getKey());

        $this->notifications->sendToRoles(
            [$type->reviewerRole()->value],
            [
                'type' => 'approval',
                'category' => 'approval',
                'priority' => 'high',
                'icon' => 'file-text',
                'title' => __('Approval requested: :label', ['label' => $type->label()]),
                'body' => __(':name requested approval. Review it from the approval center.', ['name' => $requester->full_name]),
                'redirect_url' => route('approvals.show', $request),
            ]
        );

        return $request;
    }

    /**
     * Resolve a pending request. Returns the updated request.
     */
    public function review(ApprovalRequest $request, ApprovalStatus $status, User $reviewer, ?string $note = null): ApprovalRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'request' => 'This request has already been '.$request->status.'.',
            ]);
        }

        $request->update([
            'status' => $status->value,
            'reviewer_note' => $note,
            'reviewed_by_id' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ]);

        if ($status === ApprovalStatus::Approved) {
            $this->applyEffects($request);
        }

        ActivityLogger::log($status->label().' '.$request->typeLabel().' request', 'approvals', $request->getKey(), [
            'type' => $request->type,
            'note' => $note,
        ], $reviewer->getKey());

        $this->notifications->sendToUser($request->requested_by_id, [
            'type' => 'approval',
            'category' => 'approval',
            'priority' => $status === ApprovalStatus::Approved ? 'medium' : 'low',
            'icon' => $status === ApprovalStatus::Approved ? 'check-circle' : 'x',
            'title' => __('Request :status', ['status' => strtolower($status->label())]),
            'body' => __('Your :type request was :status by :name.', [
                'type' => $request->typeLabel(),
                'status' => strtolower($status->label()),
                'name' => $reviewer->full_name,
            ]),
            'redirect_url' => route('approvals.show', $request),
        ]);

        return $request;
    }

    /**
     * Side effects applied when a request is approved.
     */
    private function applyEffects(ApprovalRequest $request): void
    {
        $type = ApprovalType::tryFrom($request->type);

        if (! $type) {
            return;
        }

        match ($type) {
            ApprovalType::StudentTransfer => $this->applyTransfer($request),
            ApprovalType::AdmissionAcceptance => $this->applyAdmission($request),
            default => null,
        };
    }

    private function applyTransfer(ApprovalRequest $request): void
    {
        $student = $request->subject_type === Student::class
            ? Student::find($request->subject_id)
            : null;

        if (! $student) {
            return;
        }

        $data = $request->data ?? [];

        $updates = ['status' => StudentStatus::Transferred->value];

        if (! empty($data['target_grade_id'])) {
            $updates['grade_level_id'] = $data['target_grade_id'];
        }

        if (! empty($data['target_class_id'])) {
            $updates['class_room_id'] = $data['target_class_id'];
        }

        $student->update($updates);
    }

    private function applyAdmission(ApprovalRequest $request): void
    {
        $student = $request->subject_type === Student::class
            ? Student::find($request->subject_id)
            : null;

        if (! $student) {
            return;
        }

        if ($student->status === StudentStatus::New->value) {
            $student->update(['status' => StudentStatus::Active->value]);
        }
    }

    /**
     * The approval requests a user is permitted to act on: their own, and any
     * pending request where they hold approval rights.
     */
    public function visibleTo(User $user): bool
    {
        return $user->hasPermission('approvals.view');
    }
}
