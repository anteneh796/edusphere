@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        <h1>{{ $title }}</h1>
        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="page-actions">{{ $slot }}</div>
    @endif
</div>