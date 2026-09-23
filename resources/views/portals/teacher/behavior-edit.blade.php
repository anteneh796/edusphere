<x-layouts.app :title="__('Edit behavior note')">
    <x-page-header :title="__('Edit behavior note')" :description="$note->student?->full_name ?? ''" />

    <x-card :title="__('Note details')">
        <form method="POST" action="{{ route('cms.teacher.behavior.update', $note) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <x-select
                    name="student_id"
                    :label="__('Student')"
                    :options="$students->mapWithKeys(fn ($s) => [$s->getKey() => $s->full_name . ' (' . ($s->classRoom?->name ?? '—') . ')'])"
                    :value="$note->student_id"
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
                :value="$note->type" />

            <x-select
                name="severity"
                :label="__('Severity')"
                :options="[
                    'info' => __('Info'),
                    'warning' => __('Warning'),
                    'serious' => __('Serious'),
                ]"
                :value="$note->severity" />

            <div class="sm:col-span-2">
                <x-input
                    name="recorded_on"
                    type="date"
                    :label="__('Date')"
                    :value="old('recorded_on', $note->recorded_on?->format('Y-m-d'))" />
            </div>

            <div class="sm:col-span-2">
                <x-textarea name="note" :label="__('Note')" :value="old('note', $note->note)" required />
            </div>

            <div class="sm:col-span-2">
                <x-textarea name="action_taken" :label="__('Action taken')" :value="old('action_taken', $note->action_taken)" />
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('cms.teacher.behavior') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>