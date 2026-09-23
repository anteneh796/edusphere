<x-layouts.app :title="__('Attendance trend')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Trend')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.trend'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.trend'])

    <x-page-header :title="__('Attendance trend')"
        :description="__('Daily mark distribution over the selected period')" />

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
                    @foreach (\App\Domains\Academics\Models\GradeLevel::orderBy('name')->get() as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
        </form>
    </x-card>

    <x-card :title="__('Daily distribution')" class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>P</th>
                        <th>A</th>
                        <th>L</th>
                        <th>E</th>
                        <th>{{ __('Rate') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $date => $totals)
                        @php($marked = array_sum($totals))
                        @php($rate = $marked > 0 ? round((($totals['present'] + $totals['late'] + $totals['excused']) / $marked) * 100, 1) : null)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($date)->format('D, M j, Y') }}</td>
                            <td class="text-sm">{{ $totals['present'] }}</td>
                            <td class="text-sm" style="color: var(--color-danger);">{{ $totals['absent'] }}</td>
                            <td class="text-sm" style="color: var(--color-warning);">{{ $totals['late'] }}</td>
                            <td class="text-sm">{{ $totals['excused'] }}</td>
                            <td>{{ $rate !== null ? $rate.'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-empty-state icon="bar-chart" :title="__('No data')" :message="__('No matching sessions in the selected period.')" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>