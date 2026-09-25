<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Department;
use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\EmploymentContract;
use App\Domains\HumanResources\Models\Position;
use App\Support\ActivityLogger;
use App\Support\Enums\ContractRenewalStatus;
use App\Support\Enums\EmploymentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('hr.view');

        $contracts = EmploymentContract::query()
            ->with(['employee:id,full_name,employee_id', 'position:id,name', 'department:id,name'])
            ->when($request->filled('renewal_status'), fn ($query, $status) => $query->where('renewal_status', $status))
            ->when($request->filled('employment_type'), fn ($query, $type) => $query->where('employment_type', $type))
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('hr.contracts.index', compact('contracts'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.view');

        $employees = $this->activeEmployees();

        return view('hr.contracts.create', ['employees' => $employees, 'contract' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.view');

        $contract = EmploymentContract::create($this->validated($request));

        ActivityLogger::log('created contract '.$contract->contract_number, 'hr', $contract->getKey());

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('status', 'Contract "'.$contract->contract_number.'" created.');
    }

    public function show(EmploymentContract $contract): View
    {
        $this->requirePermission('hr.view');

        $contract->load(['employee.department', 'employee.position', 'position', 'department', 'employee.contracts']);

        return view('hr.contracts.show', compact('contract'));
    }

    public function edit(EmploymentContract $contract): View
    {
        $this->requirePermission('hr.view');

        $employees = $this->activeEmployees();

        return view('hr.contracts.edit', compact('contract', 'employees'));
    }

    public function update(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $this->requirePermission('hr.view');

        $contract->update($this->validated($request, $contract));

        ActivityLogger::log('updated contract '.$contract->contract_number, 'hr', $contract->getKey());

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('status', 'Contract updated.');
    }

    public function renew(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $this->requirePermission('hr.view');

        $validated = $request->validate([
            'renewal_status' => ['required', Rule::enum(ContractRenewalStatus::class)],
            'new_end_date' => ['nullable', 'date', 'after:start_date'],
        ]);

        if ($validated['renewal_status'] === ContractRenewalStatus::Renewed->value && ! empty($validated['new_end_date'])) {
            $contract->update([
                'end_date' => $validated['new_end_date'],
                'renewal_status' => ContractRenewalStatus::Active->value,
            ]);
        } else {
            $contract->update(['renewal_status' => $validated['renewal_status']]);
        }

        ActivityLogger::log('renewed contract '.$contract->contract_number, 'hr', $contract->getKey());

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('status', 'Contract renewal updated.');
    }

    public function destroy(EmploymentContract $contract): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        $contractNumber = $contract->contract_number;
        $contract->delete();

        ActivityLogger::log('deleted contract '.$contractNumber, 'hr', null);

        return redirect()->route('hr.contracts.index')->with('status', 'Contract "'.$contractNumber.'" deleted.');
    }

    private function activeEmployees(): \Illuminate\Support\Collection
    {
        return Employee::query()
            ->whereIn('employment_status', ['active', 'probation'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_id']);
    }

    private function validated(Request $request, EmploymentContract $contract = null): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'contract_number' => ['required', 'string', 'max:50', Rule::unique('employment_contracts', 'contract_number')->ignore($contract?->getKey())],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'position_id' => ['nullable', 'exists:positions,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary_grade' => ['nullable', 'string', 'max:20'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'working_hours_per_week' => ['nullable', 'integer', 'min:1', 'max:80'],
            'renewal_status' => ['required', Rule::enum(ContractRenewalStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}