<?php

namespace App\Domains\HumanResources\Controllers;

use App\Domains\HumanResources\Models\Employee;
use App\Domains\HumanResources\Models\TrainingRecord;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingRecordController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('hr.view');

        $records = TrainingRecord::query()
            ->with(['employee:id,full_name,employee_id'])
            ->latest('trained_on')
            ->paginate(15);

        return view('hr.training.index', compact('records'));
    }

    public function create(): View
    {
        $this->requirePermission('hr.create');

        $employees = Employee::whereIn('employment_status', ['active', 'probation'])->orderBy('full_name')->get(['id', 'full_name', 'employee_id']);

        return view('hr.training.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('hr.create');

        $record = TrainingRecord::create($this->validated($request));

        ActivityLogger::log('added training "'.$record->course_name.'"', 'hr', $record->getKey());

        return redirect()
            ->route('hr.employees.show', ['employee' => $record->employee_id])
            ->with('status', 'Training record added.');
    }

    public function update(Request $request, TrainingRecord $record): RedirectResponse
    {
        $this->requirePermission('hr.edit');

        $record->update($this->validated($request));

        ActivityLogger::log('updated training "'.$record->course_name.'"', 'hr', $record->getKey());

        return back()->with('status', 'Training record updated.');
    }

    public function destroy(TrainingRecord $record): RedirectResponse
    {
        $this->requirePermission('hr.delete');

        $record->delete();

        ActivityLogger::log('deleted training "'.$record->course_name.'"', 'hr', null);

        return back()->with('status', 'Training record deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'course_name' => ['required', 'string', 'max:150'],
            'provider' => ['nullable', 'string', 'max:150'],
            'trained_on' => ['required', 'date'],
            'completed_on' => ['nullable', 'date', 'after_or_equal:trained_on'],
            'hours' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function requirePermission(string $permission): void
    {
        abort_unless(auth()->user()->hasPermission($permission), 403);
    }
}