<x-layouts.app :title="__('HR Reports')">

    <x-page-header :title="__('HR Reports')" :description="__('Insights across staffing, attendance, leave and contracts.')" />

    <div class="grid gap-2" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <x-stat-card :icon="'users'" :color="'primary'" :value="$counts['employees']" :label="__('Active employees')" />
        <x-stat-card :icon="'building'" :color="'accent'" :value="$counts['departments']" :label="__('Departments')" />
        <x-stat-card :icon="'calendar'" :color="'success'" :value="$counts['attendance_today']" :label="__('Attendance marked today')" />
        <x-stat-card :icon="'briefcase'" :color="'info'" :value="$counts['approved_leaves']" :label="__('Approved leave this month')" />
        <x-stat-card :icon="'file-text'" :color="'warning'" :value="$counts['expiring_contracts']" :label="__('Contracts expiring (60 days)')" />
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-3); margin-top: var(--space-3);">
        <x-card :title="__('Staffing')" style="border-left:4px solid var(--color-primary);">
            <p class="text-sm">{{ __('Review the workforce by department, employment type and status.') }}</p>
            <a href="{{ route('hr.reports.employees') }}" class="btn btn-primary btn-sm">{{ __('Employee report') }}</a>
        </x-card>
        <x-card :title="__('Attendance')" style="border-left:4px solid var(--color-success);">
            <p class="text-sm">{{ __('Month by month presence, lateness and absence per employee.') }}</p>
            <a href="{{ route('hr.reports.attendance') }}" class="btn btn-success btn-sm">{{ __('Attendance report') }}</a>
        </x-card>
        <x-card :title="__('Leave')" style="border-left:4px solid var(--color-info);">
            <p class="text-sm">{{ __('Approved leave within a period, broken down by leave type.') }}</p>
            <a href="{{ route('hr.reports.leave') }}" class="btn btn-accent btn-sm">{{ __('Leave report') }}</a>
        </x-card>
        <x-card :title="__('Contracts')" style="border-left:4px solid var(--color-warning);">
            <p class="text-sm">{{ __('Contracts expiring soon so renewals are never missed.') }}</p>
            <a href="{{ route('hr.reports.contracts') }}" class="btn btn-warning btn-sm">{{ __('Contract report') }}</a>
        </x-card>
    </div>

</x-layouts.app>