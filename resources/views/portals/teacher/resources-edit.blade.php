<x-layouts.app :title="__('Edit resource')">
    <x-breadcrumb :items="[
        ['label' => __('Resources'), 'url' => route('cms.teacher.resources')],
        ['label' => __('Edit resource')],
    ]" />

    <x-page-header :title="__('Edit resource')" :description="__('Update the details of this teaching resource.')" />

    <x-card :title="$resource->title">
        <form method="POST" action="{{ route('cms.teacher.resources.update', $resource) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <x-select
                    name="class_subject_id"
                    :label="__('Class subject')"
                    :options="$subjects->mapWithKeys(fn ($s) => [$s->getKey() => ($s->subject?->name ?? '—') . ' — ' . ($s->classRoom?->name ?? '')])"
                    :value="old('class_subject_id', $resource->class_subject_id)"
                    placeholder="{{ __('Select subject…') }}"
                    required />
            </div>

            <div class="sm:col-span-2">
                <x-input
                    name="title"
                    :label="__('Title')"
                    :value="old('title', $resource->title)"
                    required />
            </div>

            <x-select
                name="type"
                :label="__('Type')"
                :options="[
                    'document' => __('Document'),
                    'worksheet' => __('Worksheet'),
                    'presentation' => __('Presentation'),
                    'video' => __('Video'),
                    'link' => __('Link'),
                    'image' => __('Image'),
                ]"
                :value="old('type', $resource->type)" />

            <x-select
                name="visibility"
                :label="__('Visibility')"
                :options="[
                    'teacher' => __('Only me'),
                    'subject' => __('Subject'),
                    'school' => __('School'),
                ]"
                :value="old('visibility', $resource->visibility)" />

            <x-input
                name="external_url"
                :label="__('External URL')"
                :value="old('external_url', $resource->external_url)" />

            <x-input
                name="unit"
                :label="__('Unit')"
                :value="old('unit', $resource->unit)" />

            <div class="sm:col-span-2">
                <x-textarea name="description" :label="__('Description')" :value="old('description', $resource->description)" />
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('cms.teacher.resources') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>