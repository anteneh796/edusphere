<x-layouts.app :title="__('New assessment')">
    <x-page-header :title="__('New assessment')" :description="__('Create an assessment for a class.')" />

    <x-card :title="__('Assessment details')">
        <form method="POST" action="{{ route('cms.teacher.assessments.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="sm:col-span-2">
                <x-select
                    name="class_subject_id"
                    :label="__('Class subject')"
                    :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                    placeholder="{{ __('Select subject…') }}"
                    required />
            </div>

            <div class="sm:col-span-2">
                <x-select
                    name="class_room_id"
                    :label="__('Class room')"
                    :options="$classRooms->mapWithKeys(fn ($c) => [$c->getKey() => $c->name . ' — ' . ($c->gradeLevel?->name ?? '')])"
                    placeholder="{{ __('Select class…') }}"
                    required />
            </div>

            <div class="sm:col-span-2">
                <x-input
                    name="title"
                    :label="__('Title')"
                    :value="old('title')"
                    placeholder="{{ __('e.g. Unit 3 quiz') }}"
                    required />
            </div>

            <x-select
                name="type"
                :label="__('Type')"
                :options="[
                    'quiz' => __('Quiz'),
                    'classwork' => __('Classwork'),
                    'homework' => __('Homework'),
                    'practical' => __('Practical'),
                    'oral' => __('Oral'),
                    'project' => __('Project'),
                ]"
                value="quiz" />

            <x-select
                name="status"
                :label="__('Status')"
                :options="[
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'closed' => __('Closed'),
                    'graded' => __('Graded'),
                ]"
                value="draft" />

            <x-input
                name="total_marks"
                type="number"
                :label="__('Total marks')"
                :value="old('total_marks', '100')" />

            <x-input
                name="assessment_date"
                type="date"
                :label="__('Assessment date')"
                :value="old('assessment_date')" />

            <x-input
                name="room"
                :label="__('Room')"
                :value="old('room')" />

            <x-textarea name="instructions" :label="__('Instructions')" :value="old('instructions')" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Create assessment') }}</button>
                <a href="{{ route('cms.teacher.assessments') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>