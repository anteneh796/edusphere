<x-layouts.app :title="__('HR Dashboard')">

    <x-page-header :title="__('Human Resources')" :description="__('Staff lifecycle, attendance, leave, performance and payroll management.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.employees.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" class="icon-sm" />
                {{ __('Add employee') }}
            </a>
        @endif
    </x-page-header>

    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <x-stat-card icon="users" :label="__('Total employees')" :value="$data['totalEmployees']" color="primary" />
        <x-stat-card icon="book-open" :label="__('Academic staff')" :value="$data['academicStaff']" color="accent" />
        <x-stat-card icon="calendar" :label="__('On leave today')" :value="$data['onLeave']" color="warning" />
        <x-stat-card icon="briefcase" :label="__('Contracts expiring (90d)')" :value="$data['expiringContracts']" color="danger" />
        <x-stat-card icon="user-check" :label="__('New in last 30 days')" :value="$data['newEmployees']" color="success" />
        <x-stat-card icon="cake" :label="__('Birthdays today')" :value="$data['todayBirthdays']" color="info" />
        <x-stat-card icon="inbox" :label="__('Pending leave')" :value="$data['pendingLeaves']" color="neutral" />
    </div>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-3);">
        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Today\u2019s attendance')">
                @if ($attendance['records']->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Check in') }}</th>
                                    <th class="text-right">{{ __('Check out') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendance['records'] as $record)
                                    <tr>
                                        <td>
                                            <div style="font-weight: var(--weight-semibold);">{{ $record->employee->full_name }}</div>
                                            <div class="text-xs text-muted">{{ $record->employee->employee_id }}</div>
                                        </td>
                                        <td>
                                            <x-badge :color="\App\Support\Enums\StaffAttendanceStatus::from($record->status)->badgeColor()">
                                                {{ \App\Support\Enums\StaffAttendanceStatus::from($record->status)->label() }}
                                            </x-badge>
                                        </td>
                                        <td class="text-sm text-muted">{{ $record->check_in ?? '—' }}</td>
                                        <td class="text-sm text-muted">{{ $record->check_out ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state icon="clipboard-check" :title="__('No attendance yet today')" :message="__('Record staff attendance to see it here.')">
                        @if (auth()->user()->hasPermission('hr.create'))
                            <a href="{{ route('hr.attendance.take') }}" class="btn btn-primary btn-sm">{{ __('Take attendance') }}</a>
                        @endif
                    </x-empty-state>
                @endif
                <div class="card-footer flex gap-1 flex-wrap" style="align-items:center;">
                    @foreach ($attendance['summary'] as $status => $count)
                        @if ($count > 0)
                            <x-badge :color="\App\Support\Enums\StaffAttendanceStatus::from($status)->badgeColor()">
                                {{ \App\Support\Enums\StaffAttendanceStatus::from($status)->label() }}: {{ $count }}
                            </x-badge>
                        @endif
                    @endforeach
                </div>
            </x-card>

            <x-card :title="__('Headcount by department')">
                @if ($headcountByDepartment)
                    <div style="display:flex; flex-direction:column; gap: var(--space-2); padding: var(--space-1) 0;">
                        @php $max = max($headcountByDepartment); @endphp
                        @foreach ($headcountByDepartment as $name => $count)
                            <div>
                                <div class="flex" style="justify-content:space-between; align-items:center; margin-bottom:4px;">
                                    <span class="text-sm">{{ $name }}</span>
                                    <span class="text-sm text-muted">{{ $count }}</span>
                                </div>
                                <div style="height:8px; border-radius:999px; background: var(--color-border); overflow:hidden;">
                                    <div style="width: {{ $max ? round($count / $max * 100) : 0 }}%; height:100%; background: var(--color-primary);"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="layers" :title="__('No department data')" :message="__('Employees will appear here once assigned to departments.')" />
                @endif
            </x-card>
        </div>

        <div class="flex flex-col" style="gap: var(--space-3);">
            <x-card :title="__('Upcoming birthdays')">
                @if ($upcomingBirthdays->isNotEmpty())
                    <div class="flex flex-col" style="gap: var(--space-2);">
                        @foreach ($upcomingBirthdays as $employee)
                            <div class="flex" style="justify-content:space-between; align-items:center;">
                                <div>
                                    <div style="font-weight: var(--weight-semibold);">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-muted">
                                        {{ $employee->department?->name ?? 'Unassigned' }} · {{ $employee->date_of_birth->format('M j') }}
                                    </div>
                                </div>
                                <x-badge color="neutral">{{ $employee->days_until_birthday === 0 ? __('Today') : trans_choice(':n day|:n days', $employee->days_until_birthday) }}</x-badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-muted" style="padding: var(--space-2) 0;">{{ __('No birthdays in the next few days.') }}</div>
                @endif
            </x-card>

            <x-card>
                <x-slot name="actions">
                    @if (auth()->user()->hasPermission('hr.create'))
                        <a href="{{ route('hr.leave.create') }}" class="btn btn-primary btn-sm">{{ __('New request') }}</a>
                    @endif
                </x-slot>
                <x-slot:title>{{ __('Shortcuts') }}</x-slot:title>
                <div class="grid gap-1" style="grid-template-columns: repeat(2, 1fr);">
                    @if (auth()->user()->hasPermission('hr.create'))
                        <a href="{{ route('hr.attendance.take') }}" class="btn btn-secondary">{{ __('Take attendance') }}</a>
                        <a href="{{ route('hr.employees.create') }}" class="btn btn-secondary">{{ __('Add employee') }}</a>
                        <a href="{{ route('hr.candidates.create') }}" class="btn btn-secondary">{{ __('Register candidate') }}</a>
                    @endif
                    @if (auth()->user()->hasPermission('hr.payroll'))
                        <a href="{{ route('hr.contracts.create') }}" class="btn btn-secondary">{{ __('New contract') }}</a>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

</x-layouts.app>