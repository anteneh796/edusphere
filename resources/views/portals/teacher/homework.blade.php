<x-layouts.app :title="__('Homework')">
    <x-page-header :title="__('Homework')" :description="__('Assign homework and track submissions.')">
        <a href="{{ route('cms.teacher.homework.create') }}" class="btn btn-primary">{{ __('New assignment') }}</a>
    </x-page-header>

    <x-card :title="__('Assignments')">
        @forelse ($assignments as $assignment)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <a href="{{ route('cms.teacher.homework.show', $assignment) }}" class="font-medium hover:underline">
                        {{ $assignment->title }}
                    </a>
                    <p class="text-sm text-foreground-muted">
                        {{ $assignment->classSubject?->subject?->name ?? '—' }}
                        · {{ $assignment->classSubject?->classRoom?->name ?? '—' }}
                    </p>
                    <p class="text-xs text-foreground-muted mt-1">
                        {{ __('Due :date', ['date' => $assignment->due_on?->format('d M Y') ?? '—']) }}
                        @if ($assignment->max_marks)
                            · {{ __('Max :marks marks', ['marks' => $assignment->max_marks]) }}
                        @endif
                        · {{ __(':count submissions', ['count' => $assignment->submissionCount()]) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @php($color = $assignment->status === 'published' ? 'success' : 'neutral')
                    <span class="badge badge-{{ $color }}">{{ $assignment->status }}</span>
                    @if ($assignment->status !== 'published')
                        <form method="POST" action="{{ route('cms.teacher.homework.publish', $assignment) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">{{ __('Publish') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('cms.teacher.homework.show', $assignment) }}" class="text-link text-sm">{{ __('View') }}</a>
                    <a href="{{ route('cms.teacher.homework.edit', $assignment) }}" class="text-link text-sm">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('cms.teacher.homework.destroy', $assignment) }}" onsubmit="return confirm('{{ __('Delete this assignment?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="pencil"
                :title="__('No assignments yet')"
                :message="__('Create your first homework assignment.')">
                <a href="{{ route('cms.teacher.homework.create') }}" class="btn btn-primary btn-sm">{{ __('New assignment') }}</a>
            </x-empty-state>
        @endforelse
    </x-card>
</x-layouts.app>