@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'block' => false,
])

@php
    $classes = 'btn btn-'.$variant.(in_array($size, ['sm', 'xs', 'lg']) ? ' btn-'.$size : '')
        .($block ? ' btn-block' : ' btn-icon' === null ? '' : '')
        .' '.($attributes->get('class') ?? '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="icon-sm" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="icon-sm" />
        @endif
        {{ $slot }}
    </button>
@endif