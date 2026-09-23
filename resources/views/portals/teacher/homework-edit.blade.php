<x-layouts.app :title="__('Edit assignment')">
    <x-page-header :title="__('Edit assignment')" :description="$assignment->title" />

    <x-card :title="__('Assignment details')">
        <form method="POST" action="{{ route('cms.teacher.homework.update', $assignment) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <x-select
                    name="class_subject_id"
                    :label="__('Class subject')"
                    :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                    :value="$assignment->class_subject_id"
                    required />
            </div>

            <div class="sm:col-span-2">
                <x-input
                    name="title"
                    :label="__('Title')"
                    :value="old('title', $assignment->title)"
                    placeholder="{{ __('e.g. Chapter 4 exercises') }}"
                    required />
            </div>

            <x-textarea
                name="instructions"
                :label="__('Instructions')"
                :value="old('instructions', $assignment->instructions)" />

            <x-input
                name="due_on"
                type="date"
                :label="__('Due date')"
                :value="old('due_on', $assignment->due_on?->format('Y-m-d'))"
                required />

            <x-input
                name="assigned_on"
                type="date"
                :label="__('Assigned on')"
                :value="old('assigned_on', $assignment->assigned_on?->format('Y-m-d'))" />

            <x-input
                name="max_marks"
                type="number"
                :label="__('Max marks')"
                :value="old('max_marks', $assignment->max_marks)" />

            <x-select
                name="visibility"
                :label="__('Visibility')"
                :options="[
                    'class' => __('Class'),
                    'grade' => __('Grade'),
                    'school' => __('School'),
                ]"
                :value="$assignment->visibility" />

            <x-select
                name="status"
                :label="__('Status')"
                :options="[
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'closed' => __('Closed'),
                ]"
                :value="$assignment->status" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('cms.teacher.homework') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>