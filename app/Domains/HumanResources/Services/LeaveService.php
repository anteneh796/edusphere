<?php

namespace App\Domains\HumanResources\Services;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\Notifications\Services\NotificationService;
use App\Support\ActivityLogger;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\Enums\RoleName;
use App\Support\Enums\StaffAttendanceStatus;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly StaffAttendanceService $staffAttendance,
    ) {}

    public function submit(Employee $employee, array $data, User $submitter): LeaveRequest
    {
        $request = LeaveRequest::create([
            'employee_id' => $employee->getKey(),
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $data['days'] ?? $this->countDays($data['start_date'], $data['end_date']),
            'reason' => $data['reason'] ?? null,
            'status' => LeaveRequestStatus::Pending->value,
            'submitted_by_id' => $submitter->getKey(),
            'submitted_at' => now(),
        ]);

        ActivityLogger::log('submitted leave request for '.$employee->employee_id, 'hr', $request->getKey());

        $this->notifications->sendToRoles(
            [RoleName::HROfficer->value, RoleName::Principal->value, RoleName::SuperAdmin->value],
            [
                'type' => 'leave',
                'category' => 'approval',
                'priority' => 'high',
                'icon' => 'calendar',
                'title' => __('Leave request submitted by :name', ['name' => $employee->full_name]),
                'body' => __(':start to :end (:days days). Review it from the HR leave centre.', [
                    'start' => $data['start_date'],
                    'end' => $data['end_date'],
                    'days' => $data['days'] ?? $this->countDays($data['start_date'], $data['end_date']),
                ]),
                'redirect_url' => route('hr.leave.show', $request),
            ]
        );

        return $request->load('employee', 'leaveType');
    }

    public function review(LeaveRequest $request, LeaveRequestStatus $status, User $reviewer, ?string $note = null): LeaveRequest
    {
        if (! $request->isOpen()) {
            throw ValidationException::withMessages([
                'request' => 'This leave request has already been '.strtolower($request->statusLabel()).'.',
            ]);
        }

        $request->update([
            'status' => $status->value,
            'reviewed_note' => $note,
            'reviewed_by_id' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ]);

        if ($status === LeaveRequestStatus::Approved) {
            $this->staffAttendance->markOnLeaveFromRequest($request);
        }

        ActivityLogger::log($status->label().' leave request for '.$request->employee_id, 'hr', $request->getKey());

        if ($request->employee->user_id) {
            $this->notifications->sendToUser($request->employee->user_id, [
            'type' => 'leave',
            'category' => 'leave',
            'priority' => $status === LeaveRequestStatus::Approved ? 'medium' : 'low',
            'icon' => $status === LeaveRequestStatus::Approved ? 'check-circle' : 'x',
            'title' => __('Leave request :status', ['status' => strtolower($status->label())]),
            'body' => __('Your leave from :start to :end was :status.', [
                'start' => $request->start_date->toDateString(),
                'end' => $request->end_date->toDateString(),
                'status' => strtolower($status->label()),
            ]),
            'redirect_url' => route('hr.leave.show', $request),
            ]);
        }

        return $request->load('employee', 'leaveType');
    }

    public function cancel(LeaveRequest $request, User $byUser): void
    {
        if (! $request->isOpen()) {
            throw ValidationException::withMessages([
                'request' => 'This leave request can no longer be cancelled.',
            ]);
        }

        $request->update([
            'status' => LeaveRequestStatus::Cancelled->value,
            'reviewed_by_id' => $byUser->getKey(),
            'reviewed_at' => now(),
        ]);

        ActivityLogger::log('cancelled leave request for '.$request->employee_id, 'hr', $request->getKey());
    }

    private function countDays(string $start, string $end): int
    {
        return now()->parse($start)->diffInDays(now()->parse($end)) + 1;
    }
}