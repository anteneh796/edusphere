@php
    $editing = isset($department) && $department !== null;
    $department ??= null;
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" label="{{ __('Department name') }}" :value="old('name', $editing ? $department->name : null)" placeholder="e.g. Secondary School" required />
    <x-input name="code" label="{{ __('Code') }}" :value="old('code', $editing ? $department->code : null)" placeholder="e.g. SEC" required :hint="__('Short alphanumeric code, e.g. PRI, SEC, ADMIN.')" />
</div>

<x-select name="manager_user_id" :label="__('Manager account')" :options="$managers" :value="old('manager_user_id', $editing ? $department->manager_user_id : null)" placeholder="{{ __('Select a manager (optional)…') }}" :hint="__('Links a login account as the department contact.')" />

<x-textarea name="description" label="{{ __('Description') }}" :rows="3" placeholder="{{ __('What does this department do?') }}">{{ old('description', $editing ? $department->description : null) }}</x-textarea>