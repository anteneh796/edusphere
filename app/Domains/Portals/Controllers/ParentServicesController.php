<?php

namespace App\Domains\Portals\Controllers;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\ParentPortal\Models\AbsenceRequest;
use App\Domains\ParentPortal\Models\ParentRequest;
use App\Domains\Students\Models\Guardian;
use App\Http\Controllers\Controller;
use App\Support\Enums\AbsenceRequestStatus;
use App\Support\Enums\ParentRequestStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentServicesController extends Controller
{
    /* ------------------------------- Absence requests ----------------------------- */

    public function absencesIndex(): View
    {
        return view('parent-services.absences.index', [
            'absences' => AbsenceRequest::query()
                ->with(['guardian', 'student.classRoom.gradeLevel', 'reviewedBy'])
                ->latest()
                ->get(),
        ]);
    }

    public function absencesReview(AbsenceRequest $absence): RedirectResponse
    {
        $validated = request()->validate([
            'status' => ['required', Rule::in([
                AbsenceRequestStatus::Approved->value,
                AbsenceRequestStatus::Rejected->value,
            ])],
            'reviewer_note' => ['nullable', 'string', 'max:190'],
        ]);

        $absence->update([
            'status' => $validated['status'],
            'reviewer_note' => $validated['reviewer_note'] ?? null,
            'reviewed_by_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyParent($absence->guardian_id, $absence->student->full_name, [
            'type' => 'absence',
            'category' => 'attendance',
            'priority' => 'medium',
            'icon' => 'clipboard-check',
            'title' => $validated['status'] === AbsenceRequestStatus::Approved->value
                ? __('Absence request approved')
                : __('Absence request rejected'),
            'body' => __('The absence explanation for :student on :date was :status.', [
                'student' => $absence->student->full_name,
                'date' => $absence->absence_date?->format('d M Y'),
                'status' => $validated['status'],
            ]),
            'redirect_url' => route('cms.parent.absence-requests'),
        ]);

        return to_route('parent-services.absences.index')
            ->with('status', __('Absence request marked as :status.', ['status' => $validated['status']]));
    }

    /* --------------------------------- Requests --------------------------------- */

    public function requestsIndex(): View
    {
        return view('parent-services.requests.index', [
            'requests' => ParentRequest::query()
                ->with(['guardian', 'student.classRoom.gradeLevel', 'assignedTo'])
                ->latest()
                ->get(),
        ]);
    }

    public function requestsProcess(ParentRequest $request): RedirectResponse
    {
        $validated = request()->validate([
            'status' => ['required', Rule::in([
                ParentRequestStatus::Processing->value,
                ParentRequestStatus::Completed->value,
            ])],
            'resolution' => ['nullable', 'string', 'max:190'],
        ]);

        $request->update([
            'status' => $validated['status'],
            'resolution' => $validated['resolution'] ?? null,
            'assigned_to_id' => $validated['status'] === ParentRequestStatus::Processing->value
                ? auth()->id()
                : $request->assigned_to_id,
            'completed_at' => $validated['status'] === ParentRequestStatus::Completed->value ? now() : null,
        ]);

        $this->notifyParent($request->guardian_id, (string) $request->student?->full_name, [
            'type' => 'request',
            'category' => 'system',
            'priority' => 'low',
            'icon' => 'inbox',
            'title' => $validated['status'] === ParentRequestStatus::Completed->value
                ? __('Request completed')
                : __('Request in progress'),
            'body' => __('Your request :ref (:subject) is now :status.', [
                'ref' => $request->reference_number,
                'subject' => $request->subject,
                'status' => $validated['status'],
            ]),
            'redirect_url' => route('cms.parent.requests'),
        ]);

        return to_route('parent-services.requests.index')
            ->with('status', __('Request marked as :status.', ['status' => $validated['status']]));
    }

    private function notifyParent(string $parentId, string $studentName, array $payload): void
    {
        $userIds = Guardian::query()
            ->whereKey($parentId)
            ->value('user_id');

        if (! $userIds) {
            return;
        }

        app(NotificationService::class)->sendToUser($userIds, $payload);
    }
}
