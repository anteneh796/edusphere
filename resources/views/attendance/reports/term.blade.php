<x-layouts.app :title="__('Term attendance report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Term')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.term'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.term'])

    <x-page-header :title="__('Term report')"
        :description="__('End-of-term attendance performance across all classes')" />

    <x-card class="mt-4">
        <form method="GET" class="flex" style="gap: var(--space-2); flex-wrap: wrap; align-items: end;">
            <div>
                <label class="text-xs text-muted">{{ __('Academic year') }}</label>
                <select name="academic_year_id" class="form-select">
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-muted">{{ __('Term') }}</label>
                <select name="term_id" class="form-select">
                    @forelse ($terms as $termOption)
                        <option value="{{ $termOption->id }}" @selected(request('term_id') == $termOption->id)>{{ $termOption->name }}</option>
                    @empty
                        <option value="">{{ __('No terms') }}</option>
                    @endforelse
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
            <a href="{{ route('attendance.reports.term') }}" class="btn btn-ghost">{{ __('Reset') }}</a>
        </form>
    </x-card>

    @if (! $term)
        <x-card class="mt-4">
            <x-empty-state icon="calendar" :title="__('Select a term')"
                :message="__('Choose a term to view its end-of-term attendance report.')" />
        </x-card>
    @else
        <div class="grid grid-stats mt-4">
            <x-stat-card :label="__('Marked')" :value="$rows->sum('present') + $rows->sum('absent') + $rows->sum('late') + $rows->sum('excused')" icon="clipboard-check" color="info" />
            <x-stat-card :label="__('Present')" :value="$rows->sum('present')" icon="check-circle" color="success" />
            <x-stat-card :label="__('Absent')" :value="$rows->sum('absent')" icon="alert-triangle" color="danger" />
            <x-stat-card :label="__('Late')" :value="$rows->sum('late')" icon="clock" color="warning" />
            <x-stat-card :label="__('Rate')" :value="($rows->sum('present') + $rows->sum('late') + $rows->sum('excused')) > 0
                ? round((($rows->sum('present') + $rows->sum('late') + $rows->sum('excused')) / ($rows->sum('present') + $rows->sum('absent') + $rows->sum('late') + $rows->sum('excused'))) * 100, 1).'%'
                : '—'"
                icon="pie-chart" color="accent" />
        </div>

        <x-card :title="__('Students')" class="mt-4">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('No.') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>P</th>
                            <th>A</th>
                            <th>L</th>
                            <th>E</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('attendance.reports.student', ['student_id' => $row['student']->id, 'from' => $from, 'to' => $to]) }}"
                                        style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                        {{ $row['student']->full_name }}
                                    </a>
                                </td>
                                <td><span class="code-chip">{{ $row['student']->student_number }}</span></td>
                                <td class="text-sm text-muted">{{ $row['student']->classRoom?->name ?? '—' }}</td>
                                <td class="text-sm">{{ $row['present'] }}</td>
                                <td class="text-sm" style="color: var(--color-danger);">{{ $row['absent'] }}</td>
                                <td class="text-sm" style="color: var(--color-warning);">{{ $row['late'] }}</td>
                                <td class="text-sm">{{ $row['excused'] }}</td>
                                <td class="text-sm">{{ $row['total'] }}</td>
                                <td>{{ $row['rate'] !== null ? $row['rate'].'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"><x-empty-state icon="clipboard-check" :title="__('No records')" :message="__('No attendance records were found within this term.')" /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</x-layouts.app>
