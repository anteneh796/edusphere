<x-layouts.app :title="__('Attendance alerts')">

    <x-breadcrumb :items="[
        ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ['label' => __('Alerts')],
    ]" />

    @include('attendance.partials._nav', ['activeTab' => 'attendance.alerts'])

    <x-page-header :title="__('Attendance alerts')"
        :description="__('Students flagged this month — :absentAbsences+ absences or :lateLates+ lates', [
            'absentAbsences' => $absentThreshold,
            'lateLates' => $lateThreshold,
        ])" />

    <x-card class="mt-4">
        <x-slot:actions>
            <a href="{{ route('attendance.settings') }}" class="btn btn-ghost btn-sm">{{ __('Adjust thresholds') }}</a>
        </x-slot:actions>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('No.') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Absent') }}</th>
                        <th>{{ __('Late') }}</th>
                        <th>{{ __('Flags') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td style="font-weight: var(--weight-semibold);">{{ $row['student']->full_name }}</td>
                            <td><span class="code-chip">{{ $row['student']->student_number }}</span></td>
                            <td class="text-sm text-muted">{{ $row['student']->classRoom?->name ?? '—' }}</td>
                            <td style="color: var(--color-danger);">{{ $row['absent'] }}</td>
                            <td style="color: var(--color-warning);">{{ $row['late'] }}</td>
                            <td>
                                <div style="display:flex; gap: 6px; flex-wrap: wrap;">
                                    @foreach ($row['flags'] as $flag)
                                        <x-badge color="danger">{{ $flag }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="check-circle" :title="__('All clear')"
                                    :message="__('No students exceed the current absence or lateness thresholds this month.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>