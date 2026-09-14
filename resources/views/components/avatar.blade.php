@props([
    'src' => null,
    'alt' => '',
    'size' => 'md',
    'initials' => null,
])

@if ($src)
    <span {{ $attributes->merge(['class' => 'avatar avatar-'.$size]) }}>
        <img src="{{ $src }}" alt="{{ $alt }}" @error('lazy') loading="lazy" @enderror />
    </span>
@else
    <span {{ $attributes->merge(['class' => 'avatar avatar-'.$size]) }}>
        {{ $initials }}
    </span>
@endif