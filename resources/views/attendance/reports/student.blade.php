<x-layouts.app :title="__('Student attendance report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Student')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.student'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.student'])

    <x-page-header :title="__('Student report')"
        :description="__('Attendance history and summary for a single student')" />

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('Student') }}</label>
                <select name="student_id" class="form-select" style="min-width: 240px;">
                    <option value="">{{ __('Choose a student…') }}</option>
                    @foreach ($students as $option)
                        <option value="{{ $option->id }}" @selected(request('student_id') == $option->id)>
                            {{ $option->full_name }} · {{ $option->student_number }} · {{ $option->classRoom?->name ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control" />
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Run') }}</button>
        </form>
    </x-card>

    @if ($report)
        <div class="grid grid-stats mt-4">
            <x-stat-card :label="__('Records')" :value="$report['total']" icon="clipboard-check" color="primary" />
            <x-stat-card :label="__('Attended (P/L/E)')" :value="$report['summary']['present'] + $report['summary']['late'] + $report['summary']['excused']" icon="trending-up" color="success" />
            <x-stat-card :label="__('Absent')" :value="$report['summary']['absent']" icon="alert-triangle" color="danger" />
            <x-stat-card :label="__('Late')" :value="$report['summary']['late']" icon="clock" color="warning" />
            <x-stat-card :label="__('Rate')" :value="$report['rate'] !== null ? $report['rate'].'%' : '—'" icon="pie-chart" color="accent" />
        </div>

        <x-card :title="$student?->full_name ?? ''" class="mt-4">
            <x-slot:actions>
                <span class="text-sm text-muted">{{ $student?->classRoom?->name ?? '' }} · {{ $student?->student_number ?? '' }}</span>
            </x-slot:actions>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Note') }}</th>
                            <th>{{ __('Taken by') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['records'] as $record)
                            <tr>
                                <td>{{ $record->session?->date?->format('D, M j, Y') ?? '—' }}</td>
                                <td>{{ $record->session?->classRoom?->name ?? '—' }}</td>
                                <td><x-badge :color="$record->status?->badgeColor()">{{ $record->status?->label() }}</x-badge></td>
                                <td class="text-sm text-muted">{{ $record->note ?? '—' }}</td>
                                <td class="text-sm text-muted">{{ $record->session?->takenBy?->full_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><x-empty-state icon="clipboard-check" :title="__('No records')" :message="__('No attendance was recorded for this student in the selected period.')" /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    @else
        <x-card class="mt-4">
            <x-empty-state icon="user" :title="__('Choose a student')" :message="__('Pick a student above to see their attendance history.')" />
        </x-card>
    @endif
</x-layouts.app>