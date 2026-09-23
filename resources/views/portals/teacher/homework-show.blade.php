<x-layouts.app :title="__('Assignment')">
    <x-page-header :title="$assignment->title" :description="$assignment->classSubject?->subject?->name ?? '—'">
        <a href="{{ route('cms.teacher.homework') }}" class="btn btn-ghost">{{ __('Back') }}</a>
        <a href="{{ route('cms.teacher.homework.edit', $assignment) }}" class="btn btn-primary">{{ __('Edit') }}</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card :title="__('Assignment')">
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-foreground-muted">{{ __('Class') }}</dt>
                    <dd class="font-medium">{{ $assignment->classSubject?->classRoom?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-foreground-muted">{{ __('Due date') }}</dt>
                    <dd class="font-medium">{{ $assignment->due_on?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-foreground-muted">{{ __('Max marks') }}</dt>
                    <dd class="font-medium">{{ $assignment->max_marks ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-foreground-muted">{{ __('Status') }}</dt>
                    <dd class="font-medium">{{ $assignment->status }}</dd>
                </div>
            </dl>

            @if ($assignment->instructions)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold mb-2">{{ __('Instructions') }}</h4>
                    <p class="text-sm text-foreground-muted whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif
        </x-card>

        <x-card :title="__('Submissions (:count)', ['count' => $submissions->count()])">
            @forelse ($submissions as $submission)
                <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                    <div>
                        <p class="font-medium">{{ $submission->student?->full_name ?? '—' }}</p>
                        <p class="text-sm text-foreground-muted">
                            {{ $submission->submitted_at?->format('d M Y H:i') ?? '—' }}
                        </p>
                    </div>
                    @if ($submission->score !== null)
                        <span class="badge badge-success">{{ $submission->score }}{{ $assignment->max_marks ? ' / '.$assignment->max_marks : '' }}</span>
                    @else
                        <span class="badge badge-neutral">{{ __('Pending') }}</span>
                    @endif
                </div>
            @empty
                <x-empty-state
                    icon="mail"
                    :title="__('No submissions yet')"
                    :message="__('Student submissions will appear here.')" />
            @endforelse
        </x-card>
    </div>
</x-layouts.app>