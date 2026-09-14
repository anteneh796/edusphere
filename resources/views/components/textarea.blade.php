@props([
    'label' => null,
    'name' => null,
    'rows' => 4,
    'placeholder' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $error = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">
            {{ $label }}
            @if ($required)
                <span class="required">*</span>
            @endif
        </label>
    @endif

    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'form-textarea'.($error ? ' is-invalid' : '')]) }}>{{ $slot }}</textarea>

    @if ($error)
        <div class="form-error">
            <x-icon name="alert-circle" class="icon-sm" />
            {{ $error }}
        </div>
    @elseif ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif
</div>