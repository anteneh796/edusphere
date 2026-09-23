<x-layouts.app :title="__('Leave Requests')">

    <x-page-header :title="__('Leave Requests')" :description="__('Review and manage all staff leave requests.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.leave.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Request leave') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.leave.index') }}" id="leave-filters" class="grid-4" style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\LeaveRequestStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="employee_id">{{ __('Employee') }}</label>
                <select id="employee_id" name="employee_id" class="form-select">
                    <option value="">{{ __('All employees') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') === $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="from">{{ __('From') }}</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="to">{{ __('To') }}</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control" />
            </div>
        </form>
        <div class="flex gap-1" style="align-items:end; padding: 0 var(--space-3) var(--space-3);">
            <button type="submit" class="btn btn-primary" form="leave-filters">{{ __('Filter') }}</button>
            @if (request()->hasAny(['status', 'employee_id', 'from', 'to']))
                <a href="{{ route('hr.leave.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
            @endif
        </div>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Days') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $request->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $request->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $request->leaveType->name }}</td>
                            <td class="text-sm">
                                {{ $request->start_date->format('M j, Y') }} – {{ $request->end_date->format('M j, Y') }}
                            </td>
                            <td class="text-sm">{{ $request->days }}</td>
                            <td>
                                <x-badge :color="$request->statusEnum()->badgeColor()" :dot="true">{{ $request->statusLabel() }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.leave.show', $request) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="calendar" :title="__('No leave requests found')" :message="__('Adjust your filters or request new leave.')">
                                    <a href="{{ route('hr.leave.create') }}" class="btn btn-primary btn-sm">{{ __('Request leave') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $requests->links() }}</div>
    </x-card>

</x-layouts.app>