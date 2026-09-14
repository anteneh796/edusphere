@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'hint' => null,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $error = $error ?? ($name ? $errors->first($name) : null);
    $values = is_array($value) ? $value : (filled($value) ? [$value] : []);
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

    <select name="{{ $name }}" id="{{ $id }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'form-select'.($error ? ' is-invalid' : '')]) }}>

        @if ($placeholder)
            <option value="" @selected(! $values && ! $required && ! $placeholder ? '' : '') @disabled($required)>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            @if (is_array($optionLabel))
                <optgroup label="{{ $optionValue }}">
                    @foreach ($optionLabel as $groupValue => $groupLabel)
                        <option value="{{ $groupValue }}" @selected(in_array($groupValue, $values))>{{ $groupLabel }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optionValue }}" @selected(in_array($optionValue, $values))>{{ $optionLabel }}</option>
            @endif
        @endforeach
    </select>

    @if ($error)
        <div class="form-error">
            <x-icon name="alert-circle" class="icon-sm" />
            {{ $error }}
        </div>
    @elseif ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif
</div>