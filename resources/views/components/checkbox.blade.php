@props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'hint' => null,
])

<div class="form-check">
    <input type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes }} />
    @if ($label)
        <span>
            {{ $label }}
            @if ($hint)
                <span class="form-check-hint">{{ $hint }}</span>
            @endif
        </span>
    @endif
</div>