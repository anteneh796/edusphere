@php
    $editing = isset($classRoom);
    $classRoom ??= null;
    $yearOptions = $academicYears->mapWithKeys(fn ($year) => [$year->getKey() => $year->name]);
    $gradeOptions = $gradeLevels->mapWithKeys(fn ($grade) => [$grade->getKey() => $grade->name]);
    $yearValue = old('academic_year_id', $editing ? $classRoom->academic_year_id : ($currentYearId ?? null));
@endphp

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-select name="grade_level_id" label="Grade" :options="$gradeOptions" :value="old('grade_level_id', $editing ? $classRoom->grade_level_id : null)" placeholder="Select grade…" required />
    <x-input name="name" label="Section name" :value="old('name', $editing ? $classRoom->name : null)" placeholder="e.g. 5 A" required />
    <x-select name="academic_year_id" label="Academic year" :options="$yearOptions" :value="$yearValue" placeholder="Select year…" required />
    <x-input name="capacity" type="number" label="Capacity" :value="old('capacity', $editing ? $classRoom->capacity : 40)" placeholder="40" hint="Maximum number of students." />
</div>