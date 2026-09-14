@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $icons = [
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger' => 'alert-circle',
        'info' => 'info',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }} x-data="dismissable" x-show="show">
    <x-icon :name="$icons[$type] ?? 'info'" class="icon-sm" />
    <div class="alert-content">{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" class="alert-close" @click="show = false" aria-label="Dismiss">
            <x-icon name="x" class="icon-sm" />
        </button>
    @endif
</div>