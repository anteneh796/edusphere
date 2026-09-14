@props([
    'label' => null,
    'value' => '0',
    'icon' => 'dashboard',
    'color' => 'primary',
    'delta' => null,
    'deltaDirection' => 'up',
])

@php
    $deltaIcon = $deltaDirection === 'down' ? 'trending-down' : ($deltaDirection === 'flat' ? 'minus' : 'trending-up');
@endphp

<a {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="stat-icon {{ $color }}">
        <x-icon :name="$icon" class="icon-lg" />
    </div>
    <div style="min-width: 0;">
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @if ($delta)
            <div class="stat-delta {{ $deltaDirection }}">
                <x-icon :name="$deltaIcon" class="icon-sm" />
                {{ $delta }}
            </div>
        @endif
    </div>
</a>