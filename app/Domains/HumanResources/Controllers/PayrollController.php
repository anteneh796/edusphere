<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\PayrollProfile;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('hr.payroll');

        $profiles = PayrollProfile::query()
            ->with(['employee:id,full_name,employee_id,department_id', 'employee.department:id,name'])
            ->when($request->filled('q'), function ($query, $search) {
                $query->whereHas('employee', function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('hr.payroll.index', compact('profiles'));
    }

    public function edit(Employee $employee): View
    {
        $this->requirePermission('hr.payroll');

        $profile = $employee->payrollProfile ?? new PayrollProfile(['employee_id' => $employee->getKey()]);

        return view('hr.payroll.edit', compact('employee', 'profile'));
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->requirePermission('hr.payroll');

        $profile = $employee->payrollProfile()->create($this->validated($request));

        ActivityLogger::log('created payroll profile for '.$employee->employee_id, 'hr', $profile->getKey());

        return redirect()->route('hr.payroll.edit', $employee)->with('status', 'Payroll profile created.');
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->requirePermission('hr.payroll');

        $employee->payrollProfile->update($this->validated($request));

        ActivityLogger::log('updated payroll profile for '.$employee->employee_id, 'hr', $employee->payrollProfile->getKey());

        return redirect()->route('hr.payroll.edit', $employee)->with('status', 'Payroll profile updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'salary_grade' => ['nullable', 'string', 'max:20'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'array'],
            'allowances.*.name' => ['nullable', 'string', 'max:60'],
            'allowances.*.amount' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:40'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', Rule::in(['bank_transfer', 'cash', 'cheque'])],
        ]);

        $allowances = [];

        foreach ($data['allowances'] ?? [] as $entry) {
            if (filled($entry['name'] ?? null) && filled($entry['amount'] ?? null)) {
                $allowances[$entry['name']] = (float) $entry['amount'];
            }
        }

        $data['allowances'] = $allowances;

        return $data;
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}