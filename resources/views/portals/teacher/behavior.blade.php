<x-layouts.app :title="__('Behavior')">
    <x-page-header :title="__('Behavior')" :description="__('Record positive notes and concerns about students.')" />

    <x-card :title="__('Record a note')">
        <form method="POST" action="{{ route('cms.teacher.behavior.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="sm:col-span-2">
                <x-select
                    name="student_id"
                    :label="__('Student')"
                    :options="$students->mapWithKeys(fn ($s) => [$s->getKey() => $s->full_name . ' (' . ($s->classRoom?->name ?? '—') . ')'])"
                    placeholder="{{ __('Select student…') }}"
                    required />
            </div>

            <x-select
                name="type"
                :label="__('Type')"
                :options="[
                    'observation' => __('Observation'),
                    'positive' => __('Positive'),
                    'concern' => __('Concern'),
                ]"
                value="observation" />

            <x-select
                name="severity"
                :label="__('Severity')"
                :options="[
                    'info' => __('Info'),
                    'warning' => __('Warning'),
                    'serious' => __('Serious'),
                ]"
                value="info" />

            <div class="sm:col-span-2">
                <x-input
                    name="recorded_on"
                    type="date"
                    :label="__('Date')"
                    :value="old('recorded_on', date('Y-m-d'))" />
            </div>

            <div class="sm:col-span-2">
                <x-textarea name="note" :label="__('Note')" :value="old('note')" required />
            </div>

            <div class="sm:col-span-2">
                <x-textarea name="action_taken" :label="__('Action taken')" :value="old('action_taken')" />
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Save note') }}</button>
            </div>
        </form>
    </x-card>

    <x-card :title="__('Behavior notes')">
        @forelse ($notes as $note)
            <div class="py-3 border-b border-border last:border-0">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium">{{ $note->student?->full_name ?? '—' }}</p>
                        <p class="text-sm text-foreground-muted">
                            {{ $note->student?->classRoom?->name ?? '—' }}
                            @if ($note->classSubject?->subject)
                                · {{ $note->classSubject->subject->name }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-neutral">{{ $note->type }}</span>
                        @php($severityColor = $note->severity === 'serious' ? 'danger' : ($note->severity === 'warning' ? 'warning' : 'neutral'))
                        <span class="badge badge-{{ $severityColor }}">{{ $note->severity }}</span>
                        @if ((string) $note->teacher_id === (string) auth()->id())
                            <a href="{{ route('cms.teacher.behavior.edit', $note) }}" class="text-link text-sm">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('cms.teacher.behavior.destroy', $note) }}" onsubmit="return confirm('{{ __('Delete this note?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm">{{ __('Delete') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
                <p class="text-sm mt-2">{{ $note->note }}</p>
                <p class="text-xs text-foreground-muted mt-1">{{ $note->recorded_on?->format('d M Y') ?? '—' }}</p>
            </div>
        @empty
            <x-empty-state
                icon="flag"
                :title="__('No behavior notes yet')"
                :message="__('Recorded notes will appear here.')" />
        @endforelse
    </x-card>
</x-layouts.app>