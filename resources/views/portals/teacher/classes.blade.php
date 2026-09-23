<x-layouts.app :title="__('My classes')">
    <x-page-header :title="__('My classes')" :description="__('Classes you teach this term.')" />

    <x-card :title="__('Classes')">
        @forelse ($subjects as $subject)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <p class="font-medium">{{ $subject->subject?->name ?? '—' }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $subject->classRoom?->gradeLevel?->name ?? '' }} · {{ $subject->classRoom?->name ?? '' }}
                    </p>
                </div>
                <a href="{{ route('cms.teacher.classes.show', $subject) }}" class="btn btn-ghost">{{ __('Open') }}</a>
            </div>
        @empty
            <x-empty-state icon="book-open" :title="__('No classes assigned')" :message="__('Your assigned classes will appear here.')" />
        @endforelse
    </x-card>
</x-layouts.app>
