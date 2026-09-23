<x-layouts.app :title="__('Staff Attendance')">

    <x-page-header :title="__('Staff Attendance')" :description="__('Daily attendance for staff as of').' '.$date->format('M j, Y')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.attendance.take', ['date' => $date->toDateString()]) }}" class="btn btn-primary">
                <x-icon name="clipboard-check" class="icon-sm" />
                {{ __('Take / update attendance') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.attendance.index') }}" class="flex gap-1" style="align-items:flex-end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="date">{{ __('Date') }}</label>
                <input type="date" id="date" name="date" value="{{ $date->toDateString() }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\StaffAttendanceStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected($status?->value === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
        </form>

        <div class="flex gap-1 flex-wrap" style="padding: 0 var(--space-3) var(--space-2);">
            @foreach ($summary as $case => $count)
                @if ($count > 0)
                    <x-badge :color="\App\Support\Enums\StaffAttendanceStatus::from($case)->badgeColor()">
                        {{ \App\Support\Enums\StaffAttendanceStatus::from($case)->label() }}: {{ $count }}
                    </x-badge>
                @endif
            @endforeach
            <x-badge color="neutral">{{ __('Missing') }}: {{ $missing->count() }}</x-badge>
        </div>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Check in') }}</th>
                        <th>{{ __('Check out') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $record->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $record->employee->employee_id }}</div>
                            </td>
                            <td>
                                <x-badge :color="\App\Support\Enums\StaffAttendanceStatus::from($record->status)->badgeColor()" :dot="true">
                                    {{ \App\Support\Enums\StaffAttendanceStatus::from($record->status)->label() }}
                                </x-badge>
                            </td>
                            <td class="text-sm">{{ $record->check_in ?? '—' }}</td>
                            <td class="text-sm">{{ $record->check_out ?? '—' }}</td>
                            <td class="actions-cell">
                                @if (auth()->user()->hasPermission('hr.create'))
                                    <form method="POST" action="{{ route('hr.attendance.update', $record) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select" style="padding:2px 6px; font-size:var(--font-sm);" onchange="this.form.submit()">
                                            @foreach (\App\Support\Enums\StaffAttendanceStatus::cases() as $case)
                                                <option value="{{ $case->value }}" @selected($record->status === $case->value)>{{ $case->label() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="clipboard-check" :title="__('No attendance recorded for this date')"
                                    :message="__('No staff have recorded attendance. Use "Take / update attendance" to mark everyone.')">
                                    <a href="{{ route('hr.attendance.take', ['date' => $date->toDateString()]) }}" class="btn btn-primary btn-sm">{{ __('Take attendance') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if ($missing->isNotEmpty())
        <x-card :title="__('Not yet recorded (:count)', ['count' => $missing->count()])" style="margin-top: var(--space-3);">
            <div class="table-responsive">
                <table class="table table-compact">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Position') }}</th>
                            <th>{{ __('Department') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($missing as $employee)
                            <tr>
                                <td>
                                    <div style="font-weight: var(--weight-semibold);">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-muted">{{ $employee->employee_id }}</div>
                                </td>
                                <td class="text-sm text-muted">{{ $employee->position?->name ?? '—' }}</td>
                                <td class="text-sm text-muted">{{ $employee->department?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

</x-layouts.app>