@php
    $editing = isset($position) && $position !== null;
    $position ??= null;
    $departmentOptions = $departments->pluck('name', 'id');
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-input name="name" label="{{ __('Position title') }}" :value="old('name', $editing ? $position->name : null)" placeholder="e.g. Mathematics Teacher" required />
    <x-input name="slug" label="{{ __('Slug') }}" :value="old('slug', $editing ? $position->slug : null)" placeholder="e.g. math-teacher" :hint="__('Leave blank to generate from the title.')" />
    <x-select name="category" :label="__('Category')" :options="$categories" :value="old('category', $editing ? $position->category : null)" placeholder="{{ __('Select category…') }}" required />
    <x-select name="department_id" :label="__('Department')" :options="$departmentOptions" :value="old('department_id', $editing ? $position->department_id : null)" placeholder="{{ __('Optional…') }}" />
</div>

<x-textarea name="description" label="{{ __('Description') }}" :rows="3" placeholder="{{ __('Responsibilities and expectations…') }}">{{ old('description', $editing ? $position->description : null) }}</x-textarea>