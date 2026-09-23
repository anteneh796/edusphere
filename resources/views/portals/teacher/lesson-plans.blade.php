<x-layouts.app :title="__('Lesson plans')">
    <x-page-header :title="__('Lesson plans')" :description="__('Plan and track your lessons.')">
        <a href="{{ route('cms.teacher.lesson-plans.create') }}" class="btn btn-primary">{{ __('New lesson plan') }}</a>
    </x-page-header>

    <x-card :title="__('Lesson plans')">
        @forelse ($lessonPlans as $lessonPlan)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <p class="font-medium">{{ $lessonPlan->topic }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $lessonPlan->classSubject?->subject?->name ?? '—' }}
                        @if ($lessonPlan->unit)
                            · {{ $lessonPlan->unit }}
                        @endif
                    </p>
                    <p class="text-xs text-foreground-muted mt-1">
                        {{ $lessonPlan->scheduled_date?->format('d M Y') ?? '—' }}
                        @if ($lessonPlan->week_number)
                            · {{ __('Week :week', ['week' => $lessonPlan->week_number]) }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @php($color = match ($lessonPlan->status) {
                        'published' => 'success',
                        'completed' => 'neutral',
                        default => 'warning',
                    })
                    <span class="badge badge-{{ $color }}">{{ $lessonPlan->status ?? 'draft' }}</span>
                    <a href="{{ route('cms.teacher.lesson-plans.edit', $lessonPlan) }}" class="text-link text-sm">{{ __('Edit') }}</a>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="book-open"
                :title="__('No lesson plans yet')"
                :message="__('Create your first lesson plan to get started.')">
                <a href="{{ route('cms.teacher.lesson-plans.create') }}" class="btn btn-primary btn-sm">{{ __('New lesson plan') }}</a>
            </x-empty-state>
        @endforelse
    </x-card>
</x-layouts.app>