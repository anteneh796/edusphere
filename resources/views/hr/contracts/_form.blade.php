@php
    $editing = isset($contract) && $contract !== null;
    $contract ??= null;
    $employeeOptions = $employees->mapWithKeys(fn ($e) => [$e->id => $e->full_name.' ('.$e->employee_id.')']);
    $typeOptions = collect(\App\Support\Enums\EmploymentType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]);
    $renewalOptions = collect(\App\Support\Enums\ContractRenewalStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]);
    $departmentOptions = \App\Domains\HumanResources\Models\Department::orderBy('name')->pluck('name', 'id');
    $positionOptions = \App\Domains\HumanResources\Models\Position::orderBy('name')->pluck('name', 'id');
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-select name="employee_id" :label="__('Employee')" :options="$employeeOptions" :value="old('employee_id', $editing ? $contract->employee_id : null)" placeholder="{{ __('Select employee…') }}" required />
    <x-input name="contract_number" label="{{ __('Contract number') }}" :value="old('contract_number', $editing ? $contract->contract_number : null)" placeholder="e.g. CTR-2026-0042" required />
    <x-select name="employment_type" :label="__('Employment type')" :options="$typeOptions" :value="old('employment_type', $editing ? $contract->employment_type : null)" required />
    <x-select name="renewal_status" :label="__('Renewal status')" :options="$renewalOptions" :value="old('renewal_status', $editing ? $contract->renewal_status : 'active')" required />
    <x-select name="department_id" :label="__('Department')" :options="$departmentOptions" :value="old('department_id', $editing ? $contract->department_id : null)" placeholder="{{ __('Optional…') }}" />
    <x-select name="position_id" :label="__('Position')" :options="$positionOptions" :value="old('position_id', $editing ? $contract->position_id : null)" placeholder="{{ __('Optional…') }}" />
    <x-input name="start_date" type="date" label="{{ __('Start date') }}" :value="old('start_date', $editing && $contract->start_date ? $contract->start_date->format('Y-m-d') : null)" required />
    <x-input name="end_date" type="date" label="{{ __('End date') }}" :value="old('end_date', $editing && $contract->end_date ? $contract->end_date->format('Y-m-d') : null)" :hint="__('Leave blank for open-ended contracts.')" />
    <x-input name="salary_grade" label="{{ __('Salary grade') }}" :value="old('salary_grade', $editing ? $contract->salary_grade : null)" placeholder="e.g. A1" />
    <x-input name="basic_salary" type="number" step="0.01" label="{{ __('Basic salary (ETB)') }}" :value="old('basic_salary', $editing ? $contract->basic_salary : null)" min="0" />
    <x-input name="working_hours_per_week" type="number" label="{{ __('Hours per week') }}" :value="old('working_hours_per_week', $editing ? $contract->working_hours_per_week : null)" min="1" max="80" />
</div>

<x-textarea name="notes" label="{{ __('Notes') }}" :rows="3" placeholder="{{ __('Terms, conditions or renewal notes…') }}">{{ old('notes', $editing ? $contract->notes : null) }}</x-textarea>