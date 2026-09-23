<x-layouts.app :title="__('Employee Report')">

    <x-page-header :title="__('Employee Report')" :description="__('Workforce by department, employment type and status.')">
        <a href="{{ route('hr.reports.index') }}" class="btn btn-secondary">{{ __('All reports') }}</a>
    </x-page-header>

    @if ($summary['total'])
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--space-2);">
            <x-stat-card :icon="'users'" :color="'primary'" :value="$summary['total']" :label="__('Total')" />
            @foreach ($summary['by_status'] as $status => $count)
                <x-stat-card :icon="'user'" :color="'neutral'" :value="$count" :label="str_replace('_', ' ', ucfirst($status))" />
            @endforeach
        </div>
    @endif

    <x-card>
        <form method="GET" action="{{ route('hr.reports.employees') }}" class="grid gap-1" style="grid-template-columns: 1fr 1fr auto auto; align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="department_id">{{ __('Department') }}</label>
                <select id="department_id" name="department_id" class="form-select">
                    <option value="">{{ __('All departments') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\EmploymentStatus::cases() as $employmentStatus)
                        <option value="{{ $employmentStatus->value }}" @selected($status === $employmentStatus->value)>{{ $employmentStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            @if ($departmentId || $status)
                <a href="{{ route('hr.reports.employees') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Joined') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $employee->department?->name ?? '—' }}</td>
                            <td class="text-sm">{{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}</td>
                            <td>
                                <x-badge :color="\App\Support\Enums\EmploymentStatus::tryFrom($employee->employment_status)?->badgeColor() ?? 'neutral'">{{ \App\Support\Enums\EmploymentStatus::tryFrom($employee->employment_status)?->label() ?? $employee->employment_status }}</x-badge>
                            </td>
                            <td class="text-sm text-muted">{{ $employee->joining_date?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="users" :title="__('No employees match')" :message="__('Adjust the filters to see more results.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</x-layouts.app>