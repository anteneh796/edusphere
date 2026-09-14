@props([
    'title',
    'description' => null,
    'icon' => 'layout',
    'tag' => null,
])

<x-layouts.pages.app>
    <x-slot name="topNav">
        <x-cms-nav.setting active="{{ $active ?? null }}" />
    </x-slot>

    <x-page-header :title="$title" :description="$description">
        @if (! $slot->isEmpty())
            {{ $slot }}
        @endif
    </x-page-header>
</x-layouts.pages.app>
