@php
    $editing = isset($section);
    $section ??= null;
    $yearOptions = $years->mapWithKeys(fn ($aYear) => [$aYear->getKey() => $aYear->name]);
    $yearValue = old('academic_year_id', $editing ? $section->academic_year_id : ($currentYearId ?? null));
    $orderOptions = collect(range(0, 20))->mapWithKeys(fn ($i) => [$i => $i == 0 ? __('Auto') : $i]);
@endphp

<div class="form-group">
    <label class="form-label" for="name">{{ __('Section letter') }} <span class="required">*</span></label>
    <input type="text" name="name" id="name" class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
        value="{{ old('name', $editing ? $section->name : null) }}"
        placeholder="e.g. A" maxlength="30" required>
    @error('name')
        <div class="form-error"><x-icon name="alert-circle" class="icon-sm" /> {{ $message }}</div>
    @enderror
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    @if (! $editing)
        <x-select name="academic_year_id" :label="__('Academic year')" :options="$yearOptions" :value="$yearValue" placeholder="{{ __('Select year…') }}" required />
    @endif
    <x-select name="sort_order" :label="__('Order')" :options="$orderOptions" :value="old('sort_order', $editing ? $section->sort_order : 0)" />
</div>