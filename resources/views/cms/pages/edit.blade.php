@php
    $pageDescription = '/'.$page->slug.str($page->subtitle ? ' — '.$page->subtitle : '')->limit(80);
@endphp

<x-layouts.app :title="__('Edit page')">
    <x-page-header
        :title="__('Edit page')"
        :description="$pageDescription">
        <a href="{{ route('cms.pages.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to pages') }}
        </a>
        <a href="{{ $page->url() }}" target="_blank" class="btn btn-outline">
            <x-icon name="eye" class="icon-sm" />
            {{ __('Preview') }}
        </a>
    </x-page-header>

    <x-card :title="__('Page content')">
        <form method="POST" action="{{ route('cms.pages.update', $page) }}">
            @csrf
            @method('PUT')

            <x-bare-field :label="__('Slug *')" for="slug">
                <x-input name="slug" id="slug" :value="old('slug', $page->slug)" />
                <x-error for="slug" />
                <div class="form-hint">{{ __('Used in the page URL. Letters, numbers, dashes only.') }}</div>
            </x-bare-field>

            <x-bare-field :label="__('Title *')" for="title">
                <x-input name="title" id="title" :value="old('title', $page->title)" required />
                <x-error for="title" />
            </x-bare-field>

            <x-bare-field :label="__('Subtitle / excerpt')" for="subtitle">
                <x-input name="subtitle" id="subtitle" :value="old('subtitle', $page->subtitle)" />
                <x-error for="subtitle" />
            </x-bare-field>

            <x-bare-field :label="__('Body *')" for="body">
                <x-textarea name="body" id="body" rows="14" class="monospace">{{ old('body', $page->body) }}</x-textarea>
                <x-error for="body" />
                <div class="form-hint">{{ __('HTML is allowed.') }}</div>
            </x-bare-field>

            <x-bare-field>
                <input type="hidden" name="published" value="0">
                <label class="checkbox-line">
                    <x-input type="checkbox" name="published" value="1" :checked="old('published', $page->published)" />
                    <span>{{ __('Published (visible on the public site)') }}</span>
                </label>
                <x-error for="published" />
            </x-bare-field>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('cms.pages.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>