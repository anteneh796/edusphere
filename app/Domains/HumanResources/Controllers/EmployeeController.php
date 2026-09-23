<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\Accounts\Models\Role;
use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\Position;
use App\Domains\HumanResources\Services\EmployeeService;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\Enums\EmploymentStatus;
use App\Support\Enums\EmploymentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    private const NON_STAFF_ROLES = ['student', 'parent'];

    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $employees = Employee::query()
            ->with(['department', 'position', 'supervisor', 'user'])
            ->when($request->filled('q'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->ofDepartment($request->query('department'))
            ->ofPosition($request->query('position'))
            ->type($request->query('employment_type'))
            ->status($request->query('status'))
            ->when($request->filled('joining_year'), fn ($query, $year) => $query->whereYear('joining_date', $year))
            ->latest('joining_date')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.directory', compact('employees', 'departments', 'positions'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        return $this->formData(compact: ['view' => 'hr.employees.create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $data = $this->validated($request, null);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('hr/photos', 'public');
        }

        $employee = $this->employeeService->create($data);

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('status', 'Employee "'.$employee->full_name.'" ('.$employee->employee_id.') created successfully.');
    }

    public function show(Employee $employee): View
    {
        $this->requirePermission('hr.view');

        $employee->load([
            'user',
            'department',
            'position',
            'supervisor:id,full_name,employee_id',
            'subordinates:id,full_name,employee_id',
            'qualifications',
            'contracts.position',
            'contracts.department',
            'leaveRequests.leaveType',
            'attendanceRecords',
            'performanceReviews.evaluator',
            'trainingRecords',
            'documents.uploadedBy',
            'payrollProfile',
            'officialLetters',
            'statusHistories.changedBy',
        ]);

        return view('hr.employees.show', ['employee' => $employee, 'canEdit' => auth()->user()->hasPermission('hr.edit')]);
    }

    public function edit(Employee $employee): View
    {
        $this->requirePermission('hr.edit');

        return $this->formData($employee);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $data = $this->validated($request, $employee);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('hr/photos', 'public');

            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
        }

        $this->employeeService->update($employee, $data);

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('status', 'Employee "'.$employee->full_name.'" updated successfully.');
    }

    public function status(Request $request, Employee $employee): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $validated = $request->validate([
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->employeeService->changeStatus($employee, EmploymentStatus::from($validated['employment_status']), $validated['notes'] ?? null);

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('status', 'Employee status updated to '.EmploymentStatus::from($validated['employment_status'])->label().'.');
    }

    /**
     * Archive: the permanent record is kept, only the status changes. Records
     * are never hard-deleted.
     */
    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $this->employeeService->changeStatus($employee, EmploymentStatus::Terminated, 'Record archived by '.auth()->user()->full_name);
        ActivityLogger::log('archived employee '.$employee->employee_id, 'hr', $employee->getKey());

        return redirect()
            ->route('hr.employees.index')
            ->with('status', 'Employee "'.$employee->full_name.'" archived. The permanent record is retained.');
    }

    private function formData(Employee $employee = null, string $view = 'hr.employees.edit'): View
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $supervisors = Employee::query()
            ->whereIn('employment_status', [EmploymentStatus::Active->value, EmploymentStatus::Probation->value])
            ->when($employee, fn ($query) => $query->whereKeyNot($employee->getKey()))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_id']);
        $roles = Role::whereNotIn('name', self::NON_STAFF_ROLES)->orderBy('label')->get();

        return view($view, compact('departments', 'positions', 'supervisors', 'roles', 'employee'));
    }

    private function validated(Request $request, ?Employee $employee): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employee?->getKey())],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'joining_date' => ['nullable', 'date'],
            'supervisor_id' => ['nullable', 'exists:employees,id'],
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'university' => ['nullable', 'string', 'max:150'],
            'qualification' => ['nullable', 'string', 'max:150'],
            'degree' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'teaching_license' => ['nullable', 'string', 'max:100'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'certifications' => ['nullable', 'string', 'max:2000'],
            'professional_skills' => ['nullable', 'string', 'max:2000'],
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}