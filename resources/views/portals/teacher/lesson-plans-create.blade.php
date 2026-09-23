<x-layouts.app :title="__('New lesson plan')">
    <x-page-header :title="__('New lesson plan')" :description="__('Create a lesson plan for one of your classes.')" />

    <x-card :title="__('Lesson plan details')">
        <form method="POST" action="{{ route('cms.teacher.lesson-plans.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="sm:col-span-2">
                <x-select
                    name="class_subject_id"
                    :label="__('Class subject')"
                    :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                    placeholder="{{ __('Select subject…') }}"
                    required />
            </div>

            <x-input
                name="topic"
                :label="__('Topic')"
                :value="old('topic')"
                placeholder="{{ __('e.g. Fractions — addition') }}"
                required />

            <x-input
                name="unit"
                :label="__('Unit')"
                :value="old('unit')" />

            <x-input
                name="scheduled_date"
                type="date"
                :label="__('Scheduled date')"
                :value="old('scheduled_date', date('Y-m-d'))"
                required />

            <x-input
                name="week_number"
                type="number"
                :label="__('Week number')"
                :value="old('week_number', '1')" />

            <x-textarea
                name="objectives"
                :label="__('Learning objectives')"
                :value="old('objectives')" />

            <x-textarea
                name="materials"
                :label="__('Materials')"
                :value="old('materials')" />

            <x-textarea
                name="activities"
                :label="__('Class activities')"
                :value="old('activities')" />

            <x-textarea
                name="assessment"
                :label="__('Assessment')"
                :value="old('assessment')" />

            <x-textarea
                name="homework"
                :label="__('Homework')"
                :value="old('homework')" />

            <x-select
                name="status"
                :label="__('Status')"
                :options="[
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'completed' => __('Completed'),
                ]"
                value="draft" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Save lesson plan') }}</button>
                <a href="{{ route('cms.teacher.lesson-plans') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>