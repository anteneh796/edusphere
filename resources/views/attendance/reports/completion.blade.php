<x-layouts.app :title="__('Attendance completion')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Reports')],
        ['label' => __('Completion')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.reports.completion'])
    @include('attendance.reports.partials._nav', ['activeTab' => 'attendance.reports.completion'])

    <x-page-header :title="__('Attendance completion')"
        :description="__('Which classes have recorded attendance in the selected period')" />

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
                    @foreach ($currentYear->classRooms->pluck('gradeLevel')->filter()->unique('id')->values() as $grade)
                        <option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
        </form>
    </x-card>

    <x-card :title="__('Classes in :year', ['year' => $currentYear->name])" class="mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Sessions') }}</th>
                        <th>{{ __('Records marked') }}</th>
                        <th>{{ __('Coverage') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td style="font-weight: var(--weight-semibold);">{{ $row['class']->name }}</td>
                            <td class="text-sm text-muted">{{ $row['class']->gradeLevel?->name ?? '—' }}</td>
                            <td>{{ $row['session_count'] }}</td>
                            <td>{{ $row['marked'] }}</td>
                            <td>
                                @if ($row['session_count'] === 0)
                                    <x-badge color="danger">{{ __('No sessions') }}</x-badge>
                                @else
                                    <x-badge color="success">{{ __('Taking records') }}</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-empty-state icon="users" :title="__('No classes')" :message="__('No classes exist for the current academic year.')" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>