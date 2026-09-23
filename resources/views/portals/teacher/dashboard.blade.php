<x-layouts.app :title="__('Teacher dashboard')">
    <x-page-header :title="__('Teacher dashboard')" :description="__('Your classes and teaching at a glance.')">
        <a href="{{ route('cms.teacher.attendance') }}" class="btn btn-ghost">{{ __('Take attendance') }}</a>
        <a href="{{ route('cms.teacher.profile') }}" class="btn btn-ghost">{{ __('My profile') }}</a>
    </x-page-header>

    <div class="grid grid-stats">
        <x-stat-card :label="__('Classes')" :value="$subjects->count()" icon="book-open" color="primary" />
        <x-stat-card :label="__('Students')" :value="$students" icon="users" color="success" />
        <x-stat-card :label="__('Sessions today')" :value="$todaySessions->count()" icon="clipboard-check" color="warning" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card :title="__('Today\'s timetable')">
            @forelse ($todayTimetable as $slot)
                <div class="flex items-center justify-between gap-4 py-2">
                    <div>
                        <p class="font-medium">{{ $slot->classSubject?->subject?->name ?? '—' }}</p>
                        <p class="text-sm text-foreground-muted">
                            {{ $slot->classRoom?->name ?? '—' }}
                            @if ($slot->room)
                                · {{ $slot->room }}
                            @endif
                        </p>
                    </div>
                    <span class="badge badge-neutral">{{ __('Period :period', ['period' => $slot->period_number]) }}</span>
                </div>
            @empty
                <x-empty-state icon="calendar" :title="__('No classes today')" :message="__('Your timetable for today will appear here.')" />
            @endforelse
        </x-card>

        <x-card :title="__('Recent lesson plans')">
            @forelse ($recentLessonPlans as $lessonPlan)
                <div class="flex items-center justify-between gap-4 py-2">
                    <div>
                        <p class="font-medium">{{ $lessonPlan->topic }}</p>
                        <p class="text-sm text-foreground-muted">{{ $lessonPlan->classSubject?->subject?->name ?? '—' }}</p>
                    </div>
                    <a href="{{ route('cms.teacher.lesson-plans.edit', $lessonPlan) }}" class="text-link text-sm">{{ __('Edit') }}</a>
                </div>
            @empty
                <x-empty-state icon="calendar-edit" :title="__('No lesson plans yet')" :message="__('Create your first lesson plan to see it here.')" />
            @endforelse
        </x-card>

        <x-card :title="__('Upcoming homework')">
            @forelse ($upcomingHomework as $assignment)
                <div class="flex items-center justify-between gap-4 py-2">
                    <div>
                        <p class="font-medium">{{ $assignment->title }}</p>
                        <p class="text-sm text-foreground-muted">{{ $assignment->classSubject?->subject?->name ?? '—' }}</p>
                    </div>
                    <span class="badge badge-success">{{ __('Published') }}</span>
                </div>
            @empty
                <x-empty-state icon="clipboard-list" :title="__('No homework yet')" :message="__('Published assignments will appear here.')" />
            @endforelse
        </x-card>

        <x-card :title="__('My classes')">
            @forelse ($subjects as $subject)
                <div class="flex items-center justify-between gap-4 py-2">
                    <div>
                        <p class="font-medium">{{ $subject->subject?->name ?? '—' }}</p>
                        <p class="text-sm text-foreground-muted">{{ $subject->classRoom?->gradeLevel?->name ?? '' }} · {{ $subject->classRoom?->name ?? '' }}</p>
                    </div>
                    <a href="{{ route('cms.teacher.classes.show', $subject) }}" class="text-link text-sm">{{ __('Open') }}</a>
                </div>
            @empty
                <x-empty-state icon="book-open" :title="__('No classes assigned')" :message="__('Your assigned classes will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>