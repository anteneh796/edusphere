<?php

namespace App\Domains\HumanResources\Services;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Support\ActivityLogger;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\Enums\StaffAttendanceStatus;
use Illuminate\Support\Carbon;

class StaffAttendanceService
{
    public function markForDate(Carbon $date, array $statuses, User $recordedBy): void
    {
        foreach ($statuses as $employeeId => $status) {
            if (blank($status)) {
                continue;
            }

            StaffAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'attendance_date' => $date->toDateString()],
                [
                    'status' => $status,
                    'recorded_by_id' => $recordedBy->getKey(),
                ]
            );
        }

        ActivityLogger::log('recorded staff attendance for '.$date->toDateString(), 'hr', null, [
            'entries' => count(array_filter($statuses)),
        ]);
    }

    public function update(StaffAttendance $record, array $data, User $recordedBy): StaffAttendance
    {
        $record->update([...$data, 'recorded_by_id' => $recordedBy->getKey()]);

        ActivityLogger::log('updated staff attendance for '.$record->employee_id, 'hr', $record->getKey());

        return $record;
    }

    /**
     * When leave is approved, automatically reflect the leave dates as
     * "on leave" attendance records.
     */
    public function markOnLeaveFromRequest(LeaveRequest $request): void
    {
        if ($request->status !== LeaveRequestStatus::Approved->value) {
            return;
        }

        $days = $request->start_date->startOfDay()->range($request->end_date->startOfDay());

        foreach ($days as $day) {
            if ($day->isWeekend()) {
                continue;
            }

            StaffAttendance::updateOrCreate(
                ['employee_id' => $request->employee_id, 'attendance_date' => $day->toDateString()],
                ['status' => StaffAttendanceStatus::OnLeave->value]
            );
        }
    }
}