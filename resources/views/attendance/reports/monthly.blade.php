<x-layouts.app :title="__('Monthly attendance report')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Monthly')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.monthly'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.monthly'])

    <x-page-header :title="__('Monthly report')"
        :description="__('Per-student attendance aggregates over a period')" />

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
                <label class="text-xs text-muted">{{ __('Class') }}</label>
                <select name="class_room_id" class="form-select">
                    <option value="">{{ __('All classes') }}</option>
                    @foreach ($currentYear->classRooms as $class)
                        <option value="{{ $class->id }}" @selected(request('class_room_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
            <a href="{{ route('attendance.reports.monthly') }}" class="btn btn-ghost">{{ __('Reset') }}</a>
        </form>
    </x-card>

    <div class="grid grid-stats mt-4">
        <x-stat-card :label="__('Present')" :value="$totals['present']" icon="check-circle" color="success" />
        <x-stat-card :label="__('Absent')" :value="$totals['absent']" icon="alert-triangle" color="danger" />
        <x-stat-card :label="__('Late')" :value="$totals['late']" icon="clock" color="warning" />
        <x-stat-card :label="__('Excused')" :value="$totals['excused']" icon="info" color="info" />
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
                            <td colspan="9"><x-empty-state icon="users" :title="__('No data')" :message="__('No attendance records in the selected period.')" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>