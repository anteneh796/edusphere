<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\Accounts\Models\User;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\LeaveRequest;
use App\Domains\HumanResources\Models\LeaveType;
use App\Domains\HumanResources\Services\LeaveService;
use App\Http\Controllers\Controller;
use App\Support\Enums\LeaveRequestStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveService $leaveService) {}

    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $requests = LeaveRequest::query()
            ->with(['employee:id,full_name,employee_id', 'leaveType:id,name,is_paid'])
            ->when($request->filled('status') && LeaveRequestStatus::tryFrom($request->query('status')), function ($query) use ($request) {
                $query->where('status', $request->query('status'));
            })
            ->when($request->filled('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->filled('from'), fn ($query, $from) => $query->whereDate('start_date', '>=', $from))
            ->when($request->filled('to'), fn ($query, $to) => $query->whereDate('end_date', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $employees = Employee::whereIn('employment_status', ['active', 'probation'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_id']);

        return view('hr.leave.index', compact('requests', 'employees'));
    }

    /**
     * Self-service: an employee previews their own leave history.
     */
    public function myLeave(): View
    {
        $this->requirePermission('hr.view');

        $employee = $this->ownEmployee();

        if (! $employee) {
            return view('hr.leave.my', ['employee' => null, 'requests' => collect()]);
        }

        $requests = $employee->leaveRequests()
            ->with('leaveType:id,name,is_paid')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hr.leave.my', compact('employee', 'requests'));
    }

    public function create(?Employee $employee = null): View
    {
        $this->requirePermission('hr.create');

        $employee = $employee ?? $this->ownEmployee();

        if (! $employee) {
            return abort(403, "You don't have an employee profile to request leave for.");
        }

        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id']);

        return view('hr.leave.create', compact('employee', 'leaveTypes', 'employees'));
    }

    public function store(Request $request, ?Employee $employee = null): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $employee = $employee ?? $this->ownEmployee();

        if (! $employee) {
            return abort(403);
        }

        $data = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestModel = $this->leaveService->submit($employee, $data, $request->user());

        return redirect()
            ->route('hr.leave.show', $requestModel)
            ->with('status', 'Leave request submitted for review.');
    }

    public function show(LeaveRequest $request): View
    {
        $this->requirePermission('hr.view');

        $request->load(['employee.user', 'leaveType', 'submittedBy:id,first_name,last_name', 'reviewedBy:id,first_name,last_name']);

        $canReview = auth()->user()->hasPermission('hr.leave.approve');

        return view('hr.leave.show', compact('request', 'canReview'));
    }

    public function review(Request $http, LeaveRequest $request): RedirectResponse
    {
        $this->requirePermission('hr.leave.approve');

        $validated = $http->validate([
            'action' => ['required', Rule::in(['approved', 'rejected'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = LeaveRequestStatus::from($validated['action']);

        $this->leaveService->review($request, $status, $http->user(), $validated['note'] ?? null);

        return redirect()
            ->route('hr.leave.show', $request)
            ->with('status', 'Leave request '.$status->label().'.');
    }

    public function cancel(Request $http, LeaveRequest $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        abort_unless(
            $http->user()->is($request->employee?->user) || $http->user()->hasPermission('hr.leave.approve'),
            403,
            'Only the requesting employee or an HR approver can cancel this request.'
        );

        $this->leaveService->cancel($request, $http->user());

        return redirect()
            ->route('hr.leave.show', $request)
            ->with('status', 'Leave request cancelled.');
    }

    private function ownEmployee(): ?Employee
    {
        return Employee::query()
            ->where('user_id', auth()->id())
            ->orWhere('email', auth()->user()->email)
            ->first();
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}