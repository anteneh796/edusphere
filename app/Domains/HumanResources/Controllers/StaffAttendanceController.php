<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Domains\HumanResources\Services\StaffAttendanceService;
use App\Http\Controllers\Controller;
use App\Support\Enums\StaffAttendanceStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    public function __construct(private readonly StaffAttendanceService $attendanceService) {}

    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $date = $request->date('date') ?? now();
        $status = $request->filled('status') ? StaffAttendanceStatus::tryFrom($request->query('status')) : null;

        $records = StaffAttendance::query()
            ->with(['employee:id,full_name,employee_id,position_id'])
            ->whereDate('attendance_date', $date->toDateString())
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest('updated_at')
            ->get();

        $summary = [];

        foreach (StaffAttendanceStatus::cases() as $case) {
            $summary[$case->value] = $records->where('status', $case->value)->count();
        }

        $empIds = $records->pluck('employee_id');

        $missing = Employee::query()
            ->whereIn('employment_status', ['active', 'probation'])
            ->with(['position:id,name', 'department:id,name'])
            ->whereNotIn('id', $empIds)
            ->get(['id', 'full_name', 'employee_id', 'position_id', 'department_id'])
            ->load('position:id,name', 'department:id,name');

        return view('hr.attendance.index', compact('date', 'records', 'summary', 'missing', 'status'));
    }

    public function take(Request $request): View
    {
        $this->requirePermission('hr.create');

        $date = $request->date('date') ?? now();

        $employees = Employee::query()
            ->whereIn('employment_status', ['active', 'probation'])
            ->with(['position:id,name', 'department:id,name'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_id', 'position_id', 'department_id'])
            ->load('position:id,name', 'department:id,name');

        $records = StaffAttendance::whereDate('attendance_date', $date->toDateString())->get()->keyBy('employee_id');

        return view('hr.attendance.take', compact('date', 'employees', 'records'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $date = $request->date('date') ?? now();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['required_with:statuses', Rule::in(collect(StaffAttendanceStatus::cases())->pluck('value')->all())],
        ]);

        $this->attendanceService->markForDate($date, $validated['statuses'] ?? [], $request->user());

        return redirect()
            ->route('hr.attendance.index', ['date' => $date->toDateString()])
            ->with('status', 'Attendance for '.$date->toDateString().' saved.');
    }

    public function update(Request $request, StaffAttendance $record): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $validated = $request->validate([
            'status' => ['required', Rule::in(collect(StaffAttendanceStatus::cases())->pluck('value')->all())],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $this->attendanceService->update($record, $validated, $request->user());

        return back()->with('status', 'Attendance record updated.');
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}