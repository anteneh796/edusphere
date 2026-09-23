@php
    $editing = isset($leaveType) && $leaveType !== null;
    $leaveType ??= null;
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" label="{{ __('Name') }}" :value="old('name', $editing ? $leaveType->name : null)" placeholder="e.g. Annual Leave" required />
    <x-input name="code" label="{{ __('Code') }}" :value="old('code', $editing ? $leaveType->code : null)" placeholder="e.g. ANNUAL" required />
    <x-input name="days_per_year" type="number" label="{{ __('Days per year') }}" :value="old('days_per_year', $editing ? $leaveType->days_per_year : null)" min="0" max="365" required />
    <div class="flex" style="gap: var(--space-3); align-items:center; padding-top: 10px;">
        <label class="form-check" style="gap: var(--space-1);">
            <input type="checkbox" name="is_paid" value="1" @checked(old('is_paid', $editing ? $leaveType->is_paid : true)) />
            <span>{{ __('Paid leave') }}</span>
        </label>
        <label class="form-check" style="gap: var(--space-1);">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $leaveType->is_active : true)) />
            <span>{{ __('Active') }}</span>
        </label>
    </div>
</div>

<x-textarea name="description" label="{{ __('Description') }}" :rows="3" placeholder="{{ __('Rules and notes for this leave category…') }}">{{ old('description', $editing ? $leaveType->description : null) }}</x-textarea>