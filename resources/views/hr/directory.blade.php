<x-layouts.app :title="__('Employee Directory')">

    <x-page-header :title="__('Employee Directory')" :description="__('All staff records with their assignments, contact info and employment status.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                {{ __('Add employee') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.employees.index') }}" id="employee-filters" class="grid-4" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="q">{{ __('Search') }}</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="{{ __('Name, employee ID, email or phone…') }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="department-filter">{{ __('Department') }}</label>
                <select id="department-filter" name="department" class="form-select">
                    <option value="">{{ __('All departments') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department') === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="position-filter">{{ __('Position') }}</label>
                <select id="position-filter" name="position" class="form-select">
                    <option value="">{{ __('All positions') }}</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected(request('position') === $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\EmploymentStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="flex gap-1" style="align-items:end; padding: 0 var(--space-3) var(--space-3);">
            <button type="submit" class="btn btn-primary" form="employee-filters">
                <x-icon name="search" class="icon-sm" />
                {{ __('Filter') }}
            </button>
            @if (request()->hasAny(['q', 'department', 'position', 'status', 'employment_type', 'joining_year']))
                <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </div>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="$employee->initials()" size="sm" />
                                    <div style="min-width:0;">
                                        <div style="font-weight: var(--weight-semibold);">{{ $employee->full_name }}</div>
                                        <div class="text-xs text-muted">{{ $employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-muted">
                                {{ $employee->department?->name ?? '—' }}
                                @if ($employee->supervisor)
                                    <div class="text-xs text-muted">{{ __('Reports to') }} {{ $employee->supervisor->full_name }}</div>
                                @endif
                            </td>
                            <td class="text-sm">{{ $employee->position?->name ?? '—' }}</td>
                            <td class="text-sm">
                                {{ $employee->joining_date?->format('M j, Y') ?? '—' }}
                                @if ($employee->user)
                                    <div class="text-xs text-success">{{ __('Has account') }}</div>
                                @endif
                            </td>
                            <td>
                                <x-badge :color="$employee->statusEnum()->badgeColor()" :dot="true">{{ $employee->statusLabel() }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.edit'))
                                    <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                        <x-icon name="pencil" class="icon-sm" />
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="users" :title="__('No employees found')" :message="__('Try adjusting your filters or add a new employee.')">
                                    <a href="{{ route('hr.employees.create') }}" class="btn btn-primary btn-sm">{{ __('Add employee') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $employees->links() }}
        </div>
    </x-card>

</x-layouts.app>