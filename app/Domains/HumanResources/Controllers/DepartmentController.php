<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('hr.view');

        $departments = Department::query()
            ->withCount([
                'employees' => fn ($query) => $query->whereIn('employment_status', ['active', 'probation', 'on_leave']),
                'positions',
            ])
            ->withCount(['employees as total_staff_count' => fn ($query) => $query->withTrashed()])
            ->orderBy('name')
            ->paginate(15);

        return view('hr.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $managers = $this->managerOptions();

        return view('hr.departments.create', compact('managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $data = $this->validated($request);

        $department = Department::create($data);

        $this->syncManagerUser($department, $request);

        ActivityLogger::log('created department '.$department->name, 'hr', $department->getKey());

        return redirect()->route('hr.departments.index')->with('status', 'Department "'.$department->name.'" created.');
    }

    public function edit(Department $department): View
    {
        $this->requirePermission('hr.edit');

        $managers = $this->managerOptions();

        return view('hr.departments.edit', compact('department', 'managers'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $data = $this->validated($request, $department);

        $department->update($data);

        $this->syncManagerUser($department, $request);

        ActivityLogger::log('updated department '.$department->name, 'hr', $department->getKey());

        return redirect()->route('hr.departments.index')->with('status', 'Department "'.$department->name.'" updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        if ($department->employees()->exists()) {
            return back()->withErrors(['department' => 'Cannot delete a department that still has assigned staff. Move or archive staff first.']);
        }

        $department->delete();

        ActivityLogger::log('deleted department '.$department->name, 'hr', $department->getKey());

        return redirect()->route('hr.departments.index')->with('status', 'Department deleted.');
    }

    private function syncManagerUser(Department $department, Request $request): void
    {
        $managerId = $request->input('manager_user_id');

        if ($managerId === 'none') {
            return;
        }

        $department->update(['manager_user_id' => $managerId ?: null]);
    }

    private function managerOptions(): \Illuminate\Support\Collection
    {
        return Employee::query()
            ->whereIn('employment_status', ['active', 'probation'])
            ->with('user:id,first_name,last_name')
            ->orderBy('full_name')
            ->get()
            ->mapWithKeys(fn (Employee $employee) => [$employee->user_id => $employee->full_name.($employee->user_id ? '' : ' (no account)')])
            ->whenEmpty(fn () => collect(['' => 'No staff with accounts available']));
    }

    private function validated(Request $request, Department $department = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department?->getKey())],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('departments', 'code')->ignore($department?->getKey())],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_user_id' => ['nullable', 'exists:users,id'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}