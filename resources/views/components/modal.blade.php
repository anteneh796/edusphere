@props([
    'title' => null,
    'size' => 'md',
    'open' => false,
])

<div x-data="{ open: {{ $open ? 'true' : 'false' }}, busy: false }" @keydown.escape.window="open = false">

    @if (isset($trigger))
        <div @click="open = true">{{ $trigger }}</div>
    @endif

    <div x-show="open"
        x-cloak
        class="modal-backdrop"
        x-transition.opacity
        @click.self="open = false">

        <div class="modal modal-{{ $size }}" x-transition x-cloak role="dialog" aria-modal="true">
            @if ($title || isset($close))
                <div class="modal-header">
                    <h3 class="modal-title">{{ $title }}</h3>
                    @isset($close)
                        <button type="button" class="modal-close" @click="open = false" aria-label="Close">
                            {{ $close }}
                        </button>
                    @else
                        <button type="button" class="modal-close" x-show="open" @click="open = false" aria-label="Close">
                            <x-icon name="x" />
                        </button>
                    @endisset
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>