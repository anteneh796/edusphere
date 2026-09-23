@php
    $editing = isset($year);
    $year ??= null;
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" :label="__('Name')" :value="old('name', $editing ? $year->name : null)" placeholder="e.g. 2027/28" required hint="{{ __('Use the format YYYY/YY') }}" />
    <div></div>
    <x-input name="start_date" type="date" :label="__('Start date')" :value="old('start_date', $editing ? $year->start_date->format('Y-m-d') : null)" required />
    <x-input name="end_date" type="date" :label="__('End date')" :value="old('end_date', $editing ? $year->end_date->format('Y-m-d') : null)" required />
</div>

@if ($editing && $year->is_current)
    <x-alert type="info">{{ __('This is the current academic year.') }}</x-alert>
@endif