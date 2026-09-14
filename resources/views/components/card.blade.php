@props([
    'title' => null,
    'subtitle' => null,
    'pad' => true,
    'hover' => false,
])

<div {{ $attributes->merge(['class' => 'card'.($hover ? ' card-hover' : '')]) }}>
    @if ($title || isset($actions))
        <div class="card-header">
            @if ($title)
                <div>
                    <h3 class="card-title">{{ $title }}</h3>
                    @if ($subtitle)
                        <div class="card-subtitle mt-1">{{ $subtitle }}</div>
                    @endif
                </div>
            @endif

            @isset($actions)
                <div class="card-actions">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    @if ($slot->isNotEmpty())
        <div class="card-body">
            {{ $slot }}
        </div>
    @endif

    @isset($footer)
        <div class="card-footer">
            {{ $footer }}
            @isset($footerActions)
                <div class="card-footer-actions">{{ $footerActions }}</div>
            @endisset
        </div>
    @endisset
</div>