@php
    $editing = isset($review) && $review !== null;
    $review ??= null;
    $employeeOptions = $employees->mapWithKeys(fn ($e) => [$e->id => $e->full_name.' ('.$e->employee_id.')']);
    $evaluatorOptions = $evaluators->mapWithKeys(fn ($u) => [$u->id => $u->first_name.' '.$u->last_name]);
@endphp

<div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--space-2); margin-bottom: var(--space-2);">
    <x-select name="employee_id" :label="__('Employee')" :options="$employeeOptions" :value="old('employee_id', $review?->employee_id)" placeholder="{{ __('Select employee…') }}" required />
    <x-input name="period" label="{{ __('Period') }}" :value="old('period', $review?->period ?? $period ?? null)" placeholder="e.g. 2026 H1" required />
    <x-select name="evaluator_id" :label="__('Evaluator')" :options="$evaluatorOptions" :value="old('evaluator_id', $review?->evaluator_id)" placeholder="{{ __('Select evaluator…') }}" />
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--space-2);">
    @php $scoreLabels = \App\Domains\HumanResources\Controllers\PerformanceReviewController::CATEGORIES; @endphp
    @foreach ($scoreLabels as $key => $label)
        <x-input name="{{ $key }}" type="number" label="{{ $label }}" :value="old($key, $review?->{$key})" min="1" max="100" placeholder="0–100" required />
    @endforeach
</div>

<hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />

<div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
    <x-textarea name="strengths" label="{{ __('Strengths') }}" :rows="4">{{ old('strengths', $review?->strengths) }}</x-textarea>
    <x-textarea name="improvements" label="{{ __('Areas for improvement') }}" :rows="4">{{ old('improvements', $review?->improvements) }}</x-textarea>
    <x-textarea name="recommendations" label="{{ __('Recommendations') }}" :rows="4" :style="style-full">{{ old('recommendations', $review?->recommendations) }}</x-textarea>
</div>