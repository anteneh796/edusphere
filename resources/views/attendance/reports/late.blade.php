<x-layouts.app :title="__('Late report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Late')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.late'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.late'])

    <x-page-header :title="__('Late report')"
        :description="__('Students ranked by the number of late arrivals over a period')" />

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
            <div>
                <label class="text-xs text-muted">{{ __('Grade') }}</label>
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
            <a href="{{ route('attendance.reports.late') }}" class="btn btn-ghost">{{ __('Reset') }}</a>
        </form>
    </x-card>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Students flagged')" :value="$rows->count()" icon="clock" color="warning" />
        <x-stat-card :label="__('Total late marks')" :value="$rows->sum('late_count')" icon="alert-triangle" color="info" />
        <x-stat-card :label="__('Late rate')" :value="$rate !== null ? $rate.'%' : '—'" icon="pie-chart" color="accent" />
    </div>

    <x-card class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('No.') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Late count') }}</th>
                        <th>{{ __('Last late') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="text-sm text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('attendance.reports.student', ['student_id' => $row['student']->getKey(), 'from' => $from, 'to' => $to]) }}"
                                    style="font-weight: var(--weight-semibold); color: var(--color-text); text-decoration:none;">
                                    {{ $row['student']->full_name }}
                                </a>
                            </td>
                            <td><span class="code-chip">{{ $row['student']->student_number }}</span></td>
                            <td class="text-sm text-muted">{{ $row['student']->classRoom?->name ?? '—' }}</td>
                            <td><x-badge color="warning">{{ $row['late_count'] }}</x-badge></td>
                            <td class="text-sm text-muted">{{ $row['last_late']?->format('D, M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="clock" :title="__('No late arrivals')"
                                    :message="__('No late arrivals were recorded in the selected period.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
