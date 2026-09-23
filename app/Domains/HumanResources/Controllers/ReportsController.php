<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use App\Domains\HumanResources\Models\StaffAttendance;
use App\Http\Controllers\Controller;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\LeaveRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('hr.reports');

        $counts = [
            'employees' => Employee::whereIn('employment_status', ['active', 'probation', 'on_leave'])->count(),
            'departments' => Department::count(),
            'attendance_today' => StaffAttendance::whereDate('attendance_date', now()->toDateString())->count(),
            'approved_leaves' => LeaveRequest::where('status', LeaveRequestStatus::Approved->value)
                ->whereDate('end_date', '>=', now()->startOfMonth()->toDateString())
                ->whereDate('start_date', '<=', now()->endOfMonth()->toDateString())
                ->count(),
            'expiring_contracts' => EmploymentContract::active()->expiringWithin(60)->count(),
        ];

        return view('hr.reports.index', compact('counts'));
    }

    public function employees(Request $request): View
    {
        $this->requirePermission('hr.reports');

        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $employees = Employee::query()
            ->with(['department:id,name', 'position:id,name', 'user:id,employee_id'])
            ->ofDepartment($departmentId)
            ->when($status, fn ($query, $status) => $query->where('employment_status', $status))
            ->orderBy('department_id')
            ->orderBy('full_name')
            ->get(['id', 'employee_id', 'full_name', 'gender', 'position_id', 'department_id', 'employment_type', 'employment_status', 'joining_date', 'email', 'phone', 'user_id']);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        $summary = [
            'total' => $employees->count(),
            'by_status' => $employees->groupBy('employment_status')->map->count(),
            'by_department' => $employees->groupBy(fn ($e) => $e->department?->name ?? 'Unassigned')->map->count(),
        ];

        return view('hr.reports.employees', compact('employees', 'departments', 'summary', 'departmentId', 'status'));
    }

    public function attendance(Request $request): View
    {
        $this->requirePermission('hr.reports');

        $month = $request->query('month', now()->format('Y-m'));

        [$year, $monthNumber] = array_map('intval', explode('-', $month));

        $summary = StaffAttendance::query()
            ->select('employee_id', 'status', DB::raw('count(*) as days'))
            ->with('employee:id,full_name,employee_id,department_id')
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $monthNumber)
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        return view('hr.reports.attendance', compact('summary', 'month'));
    }

    public function leave(Request $request): View
    {
        $this->requirePermission('hr.reports');

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());

        $requests = LeaveRequest::query()
            ->with(['employee:id,full_name,employee_id,department_id', 'leaveType:id,name'])
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '>=', $from)
            ->whereDate('end_date', '<=', $to)
            ->orderBy('start_date')
            ->get();

        $byType = $requests->groupBy(fn ($r) => $r->leaveType?->name ?? 'Other')
            ->map->sum('days');

        $leaveTypes = LeaveType::orderBy('name')->get(['id', 'name']);

        return view('hr.reports.leave', compact('requests', 'byType', 'leaveTypes', 'from', 'to'));
    }

    public function contracts(Request $request): View
    {
        $this->requirePermission('hr.reports');

        $withinDays = (int) $request->query('within_days', 90);

        $contracts = EmploymentContract::query()
            ->active()
            ->with(['employee:id,full_name,employee_id', 'position:id,name', 'department:id,name'])
            ->expiringWithin($withinDays)
            ->orderBy('end_date')
            ->get();

        return view('hr.reports.contracts', compact('contracts', 'withinDays'));
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}