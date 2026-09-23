@php
    $editing = isset($grade);
    $grade ??= null;
    $stages = collect(\App\Support\Enums\GradeStage::cases())->mapWithKeys(fn ($stage) => [$stage->value => $stage->label()]);
    $codes = \App\Support\Enums\GradeStage::offeredGrades();
    $stageValue = old('stage', $editing ? $grade->stage : null);
    $isActiveValue = old('is_active', $editing ? $grade->is_active : true);
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" :label="__('Name')" :value="old('name', $editing ? $grade->name : null)" placeholder="e.g. Grade 5" required />
    <x-input name="code" :label="__('Code')" :value="old('code', $editing ? $grade->code : null)" placeholder="e.g. 5" required hint="{{ implode(', ', array_keys($codes)) }}" />
    <x-select name="stage" :label="__('Stage')" :options="$stages" :value="$stageValue" placeholder="{{ __('Select stage…') }}" required />
    <x-select name="sort_order" :label="__('Order')" :options="collect(range(1, 20))->mapWithKeys(fn ($i) => [$i => $i])" :value="old('sort_order', $editing ? $grade->sort_order : null)" placeholder="{{ __('Auto') }}" />
</div>

<x-textarea name="description" rows="2" :label="__('Description')" :placeholder="__('Optional note about this level')">{{ old('description', $editing ? $grade->description : null) }}</x-textarea>

@if ($editing)
    <x-bare-field>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-line">
            <x-input type="checkbox" name="is_active" value="1" :checked="(bool) $isActiveValue" />
            <span>{{ __('Offered to students (KG–8 only)') }}</span>
        </label>
    </x-bare-field>
@endif