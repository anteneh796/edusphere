@php
$relationshipOptions = [
    'father' => 'Father',
    'mother' => 'Mother',
        'sibling' => 'Sibling',
];
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="first_name" label="{{ __('First name') }}" placeholder="{{ __('e.g. Almaz') }}" required />
    <x-input name="last_name" label="{{ __('Last name') }}" placeholder="{{ __('e.g. Bekele') }}" required />
    <x-select name="relationship" label="{{ __('Relationship') }}" :options="$relationshipOptions" placeholder="{{ __('Select…') }}" required />
    <x-input name="phone" label="{{ __('Phone') }}" placeholder="{{ __('+251 9xx xxx xxx') }}" />
    <x-input name="email" type="email" label="{{ __('Email') }}" placeholder="{{ __('Optional') }}" />
    <x-input name="occupation" label="{{ __('Occupation') }}" placeholder="{{ __('Optional') }}" />
    <x-input name="national_id" label="{{ __('National ID') }}" placeholder="{{ __('Optional') }}" />
    <x-input name="address" label="{{ __('Address') }}" placeholder="{{ __('Optional') }}" />
</div>

<div class="form-group">
    <label class="form-check">
        <input type="checkbox" name="is_primary" value="1" />
        <span>{{ __('Primary parent') }}</span>
    </label>
    <label class="form-check" style="margin-left: var(--space-3);">
        <input type="checkbox" name="is_emergency" value="1" />
        <span>{{ __('Emergency contact') }}</span>
    </label>
</div>