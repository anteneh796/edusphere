@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        @foreach ($items as $item)
            @if ($loop->last || ! filled($item['url'] ?? null))
                <li class="current" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li>
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    <span class="separator">
                        <x-icon name="chevron-right" class="icon-sm" />
                    </span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>