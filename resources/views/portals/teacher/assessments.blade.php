<x-layouts.app :title="__('Assessments & grades')">
    <x-page-header :title="__('Assessments & grades')" :description="__('Create assessments and record student grades.')">
        <a href="{{ route('cms.teacher.assessments.create') }}" class="btn btn-primary">{{ __('New assessment') }}</a>
    </x-page-header>

    <x-card :title="__('Assessments')">
        @forelse ($assessments as $assessment)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <p class="font-medium">{{ $assessment->title }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $assessment->classSubject?->subject?->name ?? '—' }}
                        · {{ $assessment->classRoom?->name ?? '—' }}
                    </p>
                    <p class="text-xs text-foreground-muted mt-1">
                        {{ $assessment->type }}
                        · {{ __('Total :marks marks', ['marks' => $assessment->total_marks]) }}
                        · {{ __(':count marked', ['count' => $assessment->results_count]) }}
                        @if ($assessment->assessment_date)
                            · {{ $assessment->assessment_date->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @php($color = $assessment->status === 'published' ? 'success' : 'neutral')
                    <span class="badge badge-{{ $color }}">{{ $assessment->status }}</span>
                    <a href="{{ route('cms.teacher.assessments.grades', $assessment) }}" class="text-link text-sm">{{ __('Grades') }}</a>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="award"
                :title="__('No assessments yet')"
                :message="__('Create your first assessment to start recording grades.')">
                <a href="{{ route('cms.teacher.assessments.create') }}" class="btn btn-primary btn-sm">{{ __('New assessment') }}</a>
            </x-empty-state>
        @endforelse
    </x-card>
</x-layouts.app>