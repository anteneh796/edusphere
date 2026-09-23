<x-layouts.app :title="__('Student progress')">
    <x-page-header :title="__('Student progress')" :description="__('Performance and assessment results across your classes.')" />

    <div class="grid grid-stats">
        <x-stat-card :label="__('Students')" :value="$rows->count()" icon="users" color="primary" />
        <x-stat-card :label="__('Assessments')" :value="$assessments->count()" icon="award" color="success" />
    </div>

    <x-card :title="__('Assessment average by student')">
        @if ($rows->isEmpty())
            <x-empty-state icon="trending-up" :title="__('No students yet')" :message="__('Enrolled students will appear here.')" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Assessments') }}</th>
                            <th>{{ __('Average') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['student']->full_name }}</td>
                                <td>{{ $row['student']->classRoom?->name ?? '—' }}</td>
                                <td>{{ $row['assessmentCount'] }}</td>
                                <td>{{ $row['assessmentAverage'] !== null ? $row['assessmentAverage'] : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card :title="__('Recent assessments')">
        @forelse ($assessments as $assessment)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <p class="font-medium">{{ $assessment->title }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $assessment->classSubject?->subject?->name ?? '—' }}
                        · {{ $assessment->classRoom?->name ?? '—' }}
                    </p>
                </div>
                <span class="badge badge-neutral">{{ $assessment->results->count() }} {{ __('results') }}</span>
            </div>
        @empty
            <x-empty-state icon="award" :title="__('No assessments yet')" :message="__('Create assessments to track student progress.')" />
        @endforelse
    </x-card>
</x-layouts.app>