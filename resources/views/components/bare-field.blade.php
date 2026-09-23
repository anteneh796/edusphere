@props([
    'label' => null,
    'for' => null,
])

<div class="form-group">
    @if ($label)
        <label class="form-label" @if ($for) for="{{ $for }}" @endif>{{ $label }}</label>
    @endif

    {{ $slot }}
</div>