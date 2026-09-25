<?php

namespace App\Domains\HumanResources\Services;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use App\Domains\HumanResources\Models\Position;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\LeaveRequestStatus;
use App\Support\Enums\StaffAttendanceStatus;

class HrDashboardService
{
    public function data(): array
    {
        $activeStatuses = [EmploymentStatus::Active->value, EmploymentStatus::Probation->value, EmploymentStatus::OnLeave->value];

        $totalEmployees = Employee::whereIn('employment_status', $activeStatuses)->count();

        $academicPositionIds = Position::where('category', 'academic')->pluck('id');
        $academicStaff = Employee::whereIn('employment_status', $activeStatuses)
            ->whereIn('position_id', $academicPositionIds)
            ->count();

        $today = now()->toDateString();

        $onLeave = Employee::whereIn('employment_status', $activeStatuses)
            ->whereHas('leaveRequests', fn ($query) => $query
                ->where('status', LeaveRequestStatus::Approved->value)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today))
            ->count();

        $expiringContracts = EmploymentContract::active()->expiringWithin(90)->count();

        $newEmployees = Employee::whereIn('employment_status', $activeStatuses)
            ->where('joining_date', '>=', now()->subDays(30)->toDateString())
            ->count();

        $todayBirthdays = Employee::whereIn('employment_status', $activeStatuses)
            ->whereRaw('MONTH(date_of_birth) = ? AND DAY(date_of_birth) = ?', [now()->month, now()->day])
            ->count();

        $pendingLeaves = LeaveRequest::ofStatus(LeaveRequestStatus::Pending->value)->count();

        return compact(
            'totalEmployees',
            'academicStaff',
            'onLeave',
            'expiringContracts',
            'newEmployees',
            'todayBirthdays',
            'pendingLeaves'
        );
    }

    public function birthdays(): \Illuminate\Support\Collection
    {
        return Employee::whereIn('employment_status', [
            EmploymentStatus::Active->value,
            EmploymentStatus::Probation->value,
        ])
            ->with(['department', 'position'])
            ->get()
            ->filter(function (Employee $employee) {
                if (! $employee->date_of_birth) {
                    return false;
                }

                return $employee->date_of_birth->format('m-d') === now()->format('m-d');
            })
            ->values();
    }

    public function upcomingBirthdays(int $limit = 5): \Illuminate\Support\Collection
    {
        $today = now();

        return Employee::whereIn('employment_status', [
            EmploymentStatus::Active->value,
            EmploymentStatus::Probation->value,
        ])
            ->whereNotNull('date_of_birth')
            ->with(['department', 'position'])
            ->get()
            ->map(function (Employee $employee) use ($today) {
                $birthday = $today->copy()->month($employee->date_of_birth->month)->day($employee->date_of_birth->day);

                if ($birthday->lt($today->startOfDay())) {
                    $birthday->addYear();
                }

                $employee->setAttribute('days_until_birthday', (int) $today->startOfDay()->diffInDays($birthday, false) + 1);

                return $employee;
            })
            ->filter(fn (Employee $employee) => $employee->days_until_birthday >= 0)
            ->sortBy('days_until_birthday')
            ->take($limit)
            ->values();
    }

    public function headcountByDepartment(): array
    {
        return Department::query()
            ->withCount(['employees' => fn ($query) => $query->whereIn('employment_status', [
                EmploymentStatus::Active->value,
                EmploymentStatus::Probation->value,
                EmploymentStatus::OnLeave->value,
            ])])
            ->orderByDesc('employees_count')
            ->get()
            ->filter(fn (Department $department) => $department->employees_count > 0)
            ->pluck('employees_count', 'name')
            ->all();
    }

    public function todaysAttendance(): array
    {
        $records = StaffAttendance::with('employee:id,full_name,employee_id')
            ->whereDate('attendance_date', now()->toDateString())
            ->get();

        $summary = [];

        foreach (StaffAttendanceStatus::cases() as $case) {
            $summary[$case->value] = 0;
        }

        foreach ($records as $record) {
            $summary[$record->status] = ($summary[$record->status] ?? 0) + 1;
        }

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    public function leaveTypes(): \Illuminate\Support\Collection
    {
        return LeaveType::orderBy('name')->get();
    }
}