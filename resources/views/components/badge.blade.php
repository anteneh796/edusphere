@props([
    'color' => 'neutral',
    'label' => null,
    'dot' => false,
])

<span {{ $attributes->merge(['class' => 'badge badge-'.$color]) }}>
    @if ($dot)
        <span class="badge-dot"></span>
    @endif
    {{ $label ?? $slot }}
</span>