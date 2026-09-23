@php
    $editing = isset($term);
    $term ??= null;
    $yearOptions = $years->mapWithKeys(fn ($aYear) => [$aYear->getKey() => $aYear->name]);
    $yearValue = old('academic_year_id', $editing ? $term->academic_year_id : ($currentYearId ?? null));
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" :label="__('Name')" :value="old('name', $editing ? $term->name : null)" placeholder="e.g. Term 1" required />
    <x-input name="sequence" type="number" :label="__('Sequence')" :value="old('sequence', $editing ? $term->sequence : null)" placeholder="e.g. 1" min="1" />
    @if (! $editing)
        <x-select name="academic_year_id" :label="__('Academic year')" :options="$yearOptions" :value="$yearValue" placeholder="{{ __('Select year…') }}" required />
    @endif
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="start_date" type="date" :label="__('Start date')" :value="old('start_date', $editing && $term->start_date ? $term->start_date->format('Y-m-d') : null)" />
    <x-input name="end_date" type="date" :label="__('End date')" :value="old('end_date', $editing && $term->end_date ? $term->end_date->format('Y-m-d') : null)" />
</div>

@if ($editing && $term->is_current)
    <x-alert type="info">{{ __('This is the current term.') }}</x-alert>
@endif