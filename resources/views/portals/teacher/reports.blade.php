<x-layouts.app :title="__('Reports')">
    <x-page-header :title="__('Reports & analytics')" :description="__('Assessment summaries, student performance and attendance analytics.')" />

    <div class="grid grid-stats">
        <x-stat-card :label="__('Assessments')" :value="$assessmentRows->count()" icon="award" color="primary" />
        <x-stat-card :label="__('Students')" :value="$studentRows->count()" icon="users" color="success" />
        <x-stat-card :label="__('Classes')" :value="$attendanceRows->count()" icon="book-open" color="warning" />
    </div>

    <x-card :title="__('Assessment overview')">
        @if ($assessmentRows->isEmpty())
            <x-empty-state icon="bar-chart" :title="__('No assessments yet')" :message="__('Create assessments to see reports here.')" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Assessment') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Marked') }}</th>
                            <th>{{ __('Average') }}</th>
                            <th>{{ __('Highest') }}</th>
                            <th>{{ __('Lowest') }}</th>
                            <th>{{ __('Pass rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assessmentRows as $row)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $row['assessment']->title }}</div>
                                    <div class="text-xs text-muted">{{ $row['assessment']->classSubject?->subject?->name ?? '—' }}</div>
                                </td>
                                <td>{{ $row['assessment']->classRoom?->name ?? '—' }}</td>
                                <td>{{ $row['students'] }}</td>
                                <td>{{ $row['average'] ?? '—' }}</td>
                                <td>{{ $row['highest'] ?? '—' }}</td>
                                <td>{{ $row['lowest'] ?? '—' }}</td>
                                <td>
                                    @if ($row['passRate'] !== null)
                                        @php($rate = (float) $row['passRate'])
                                        <span class="badge badge-{{ $rate >= 50 ? 'success' : ($rate >= 40 ? 'warning' : 'danger') }}">
                                            {{ $row['passRate'] }}%
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card :title="__('Student performance')">
        @if ($studentRows->isEmpty())
            <x-empty-state icon="users" :title="__('No students yet')" :message="__('Enrolled students will appear here.')" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Admission #') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Assessments taken') }}</th>
                            <th>{{ __('Average') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentRows as $row)
                            <tr>
                                <td>{{ $row['student']->full_name }}</td>
                                <td>{{ $row['student']->student_number }}</td>
                                <td>{{ $row['student']->classRoom?->name ?? '—' }}</td>
                                <td>{{ $row['assessmentsTaken'] }}</td>
                                <td>{{ $row['average'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card :title="__('Attendance by class')">
        @if ($attendanceRows->isEmpty())
            <x-empty-state icon="clipboard-check" :title="__('No classes yet')" :message="__('Attendance analytics appear once records are marked.')" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Records') }}</th>
                            <th>{{ __('Attendance rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendanceRows as $row)
                            <tr>
                                <td>{{ $row['classRoom']->name }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>
                                    @if ($row['rate'] !== null)
                                        @php($rate = (float) $row['rate'])
                                        <span class="badge badge-{{ $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger') }}">
                                            {{ $row['rate'] }}%
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-layouts.app>