<x-layouts.app :title="__('Daily attendance report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Daily')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.daily'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.daily'])

    <x-page-header :title="__('Daily report')"
        :description="__('All sessions and marks recorded on :date', ['date' => \Carbon\Carbon::parse($report['date'])->format('D, M j, Y')])" />

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('Date') }}</label>
                <input type="date" name="date" value="{{ $report['date'] }}" class="form-control" />
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('Grade level') }}</label>
                <select name="grade_level_id" class="form-select">
                    <option value="">{{ __('All grades') }}</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('Class') }}</label>
                <select name="class_room_id" class="form-select">
                    <option value="">{{ __('All classes') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
            <a href="{{ route('attendance.reports.daily') }}" class="btn btn-ghost">{{ __('Reset') }}</a>
        </form>
    </x-card>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Sessions')" :value="$report['sessions']->count()" icon="clipboard-check" color="primary" />
        <x-stat-card :label="__('Records')" :value="$report['marked']" icon="users" color="info" />
        <x-stat-card :label="__('Attended (P/L/E)')" :value="$report['totals']['present'] + $report['totals']['late'] + $report['totals']['excused']" icon="trending-up" color="success" />
        <x-stat-card :label="__('Absent')" :value="$report['totals']['absent']" icon="alert-triangle" color="danger" />
        <x-stat-card :label="__('Late')" :value="$report['totals']['late']" icon="clock" color="warning" />
        <x-stat-card :label="__('Rate')" :value="$report['rate'] !== null ? $report['rate'].'%' : '—'" icon="pie-chart" color="accent" />
    </div>

    @forelse ($report['sessions'] as $session)
        @php($summary = \App\Domains\Attendance\Services\AttendanceService::summarize($session->records))
        <x-card :title="$session->classRoom?->name ?? '—'" :subtitle="$session->classRoom?->gradeLevel?->name ?? ''" class="mt-4">
            <x-slot:actions>
                <span style="display:inline-flex; align-items:center; gap: var(--space-2);">
                    <x-badge :color="$session->status?->badgeColor()" :dot="true">{{ $session->status?->label() }}</x-badge>
                    <a href="{{ route('attendance.show', $session) }}" class="btn btn-ghost btn-sm">{{ __('Open') }}</a>
                </span>
            </x-slot:actions>
            <div class="flex" style="gap: var(--space-3); flex-wrap: wrap; margin-bottom: var(--space-3);">
                <span class="att-chip present">P · <b>{{ $summary['present'] }}</b></span>
                <span class="att-chip late">L · <b>{{ $summary['late'] }}</b></span>
                <span class="att-chip absent">A · <b>{{ $summary['absent'] }}</b></span>
                <span class="att-chip excused">E · <b>{{ $summary['excused'] }}</b></span>
                <span class="text-sm text-muted">{{ $session->records->count() }} records · taken by {{ $session->takenBy?->full_name ?? '—' }} at {{ $session->opened_at?->format('g:i A') ?? '—' }}</span>
            </div>
        </x-card>
    @empty
        <x-card class="mt-4">
            <x-empty-state icon="clipboard-check" :title="__('No sessions on this day')"
                :message="__('No attendance was recorded for the selected filters.')" />
        </x-card>
    @endforelse
</x-layouts.app>