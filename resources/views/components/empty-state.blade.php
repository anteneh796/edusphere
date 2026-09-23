@props([
    'icon' => 'file-text',
    'title' => __('Nothing here yet'),
    'message' => __('No records have been found.'),
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon">
        <x-icon :name="$icon" class="icon-lg" />
    </div>
    <h3>{{ $title }}</h3>
    <p>{{ $message }}</p>
    @if ($slot->isNotEmpty())
        <div style="display:flex; gap: var(--space-1); margin-top: var(--space-2);">{{ $slot }}</div>
    @endif
</div>