<x-layouts.app :title="__('Edit lesson plan')">
    <x-page-header :title="__('Edit lesson plan')" :description="$lessonPlan->topic" />

    <x-card :title="__('Lesson plan details')">
        <form method="POST" action="{{ route('cms.teacher.lesson-plans.update', $lessonPlan) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <x-select
                    name="class_subject_id"
                    :label="__('Class subject')"
                    :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                    :value="$lessonPlan->class_subject_id"
                    required />
            </div>

            <x-input
                name="topic"
                :label="__('Topic')"
                :value="old('topic', $lessonPlan->topic)"
                required />

            <x-input
                name="unit"
                :label="__('Unit')"
                :value="old('unit', $lessonPlan->unit)" />

            <x-input
                name="scheduled_date"
                type="date"
                :label="__('Scheduled date')"
                :value="old('scheduled_date', $lessonPlan->scheduled_date?->format('Y-m-d'))"
                required />

            <x-input
                name="week_number"
                type="number"
                :label="__('Week number')"
                :value="old('week_number', $lessonPlan->week_number)" />

            <x-textarea
                name="objectives"
                :label="__('Learning objectives')"
                :value="old('objectives', $lessonPlan->objectives)" />

            <x-textarea
                name="materials"
                :label="__('Materials')"
                :value="old('materials', $lessonPlan->materials)" />

            <x-textarea
                name="activities"
                :label="__('Class activities')"
                :value="old('activities', $lessonPlan->activities)" />

            <x-textarea
                name="assessment"
                :label="__('Assessment')"
                :value="old('assessment', $lessonPlan->assessment)" />

            <x-textarea
                name="homework"
                :label="__('Homework')"
                :value="old('homework', $lessonPlan->homework)" />

            <x-select
                name="status"
                :label="__('Status')"
                :options="[
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'completed' => __('Completed'),
                ]"
                :value="$lessonPlan->status" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('cms.teacher.lesson-plans') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>