<x-layouts.app :title="__('Grade attendance report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Grade')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.grade'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.grade'])

    <x-page-header :title="__('Grade report')"
        :description="__('Attendance aggregates grouped by grade level over a period')" />

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control" />
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
            <a href="{{ route('attendance.reports.grade') }}" class="btn btn-ghost">{{ __('Reset') }}</a>
        </form>
    </x-card>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Students')" :value="$report['grades']->sum('students')" icon="users" color="primary" />
        <x-stat-card :label="__('Records')" :value="$report['marked']" icon="clipboard-check" color="info" />
        <x-stat-card :label="__('Absent')" :value="$report['totals']['absent']" icon="alert-triangle" color="danger" />
        <x-stat-card :label="__('Late')" :value="$report['totals']['late']" icon="clock" color="warning" />
        <x-stat-card :label="__('Rate')" :value="$report['rate'] !== null ? $report['rate'].'%' : '—'" icon="pie-chart" color="accent" />
    </div>

    <x-card :title="__('Grades')" class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Classes') }}</th>
                        <th>{{ __('Students') }}</th>
                        <th>P</th>
                        <th>A</th>
                        <th>L</th>
                        <th>E</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Rate') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['grades'] as $row)
                        <tr>
                            <td style="font-weight: var(--weight-semibold);">{{ $row['grade']?->name ?? __('Ungraded') }}</td>
                            <td class="text-sm text-muted">{{ implode(', ', $row['classes']) }}</td>
                            <td class="text-sm">{{ $row['students'] }}</td>
                            <td class="text-sm">{{ $row['totals']['present'] }}</td>
                            <td class="text-sm" style="color: var(--color-danger);">{{ $row['totals']['absent'] }}</td>
                            <td class="text-sm" style="color: var(--color-warning);">{{ $row['totals']['late'] }}</td>
                            <td class="text-sm">{{ $row['totals']['excused'] }}</td>
                            <td class="text-sm">{{ $row['marked'] }}</td>
                            <td>{{ $row['rate'] !== null ? $row['rate'].'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"><x-empty-state icon="layers" :title="__('No data')" :message="__('No attendance records were found in the selected period.')" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>