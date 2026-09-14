@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'hint' => null,
    'error' => null,
    'wrapperClass' => null,
])

@php
    $id = $id ?? $name;
    $error = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="form-group {{ $wrapperClass }}">
    @if ($label)
        <label class="form-label" for="{{ $id }}">
            {{ $label }}
            @if ($required)
                <span class="required">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}"
        @if ($name) name="{{ $name }}" @endif
        id="{{ $id }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $autofocus ? 'autofocus' : '' }}
        {{ $attributes->merge(['class' => 'form-control'.($error ? ' is-invalid' : '')]) }} />

    @if ($error)
        <div class="form-error">
            <x-icon name="alert-circle" class="icon-sm" />
            {{ $error }}
        </div>
    @elseif ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif
</div>