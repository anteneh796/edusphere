<x-layouts.app :title="__('Resources')">
    <x-page-header :title="__('Resources')" :description="__('Manage teaching resources for your classes.')" />

    <x-card :title="__('Add a resource')">
        <form method="POST" action="{{ route('cms.teacher.resources.store') }}" class="grid gap-4 sm:grid-cols-2">
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
                <x-input
                    name="title"
                    :label="__('Title')"
                    :value="old('title')"
                    placeholder="{{ __('e.g. Chapter 3 slides') }}"
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
                value="document" />

            <x-select
                name="visibility"
                :label="__('Visibility')"
                :options="[
                    'teacher' => __('Only me'),
                    'subject' => __('Subject'),
                    'school' => __('School'),
                ]"
                value="teacher" />

            <x-input
                name="external_url"
                :label="__('External URL')"
                :value="old('external_url')" />

            <x-input
                name="unit"
                :label="__('Unit')"
                :value="old('unit')" />

            <x-textarea name="description" :label="__('Description')" :value="old('description')" />

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('Add resource') }}</button>
            </div>
        </form>
    </x-card>

    <x-card :title="__('Resources')">
        @forelse ($resources as $resource)
            <div class="flex items-center justify-between gap-4 py-3 border-b border-border last:border-0">
                <div>
                    <p class="font-medium">{{ $resource->title }}</p>
                    <p class="text-sm text-foreground-muted">
                        {{ $resource->classSubject?->subject?->name ?? '—' }}
                        · {{ $resource->classSubject?->classRoom?->name ?? '—' }}
                    </p>
                    @if ($resource->description)
                        <p class="text-xs text-foreground-muted mt-1">{{ $resource->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-neutral">{{ $resource->type }}</span>
                    @if ($resource->external_url)
                        <a href="{{ $resource->external_url }}" target="_blank" rel="noopener" class="text-link text-sm">{{ __('Open') }}</a>
                    @endif
                    <a href="{{ route('cms.teacher.resources.edit', $resource) }}" class="text-link text-sm">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('cms.teacher.resources.destroy', $resource) }}" onsubmit="return confirm('{{ __('Delete this resource?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="folder"
                :title="__('No resources yet')"
                :message="__('Added resources will appear here.')" />
        @endforelse
    </x-card>
</x-layouts.app>