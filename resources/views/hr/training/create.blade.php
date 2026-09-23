<x-layouts.app :title="__('Add Training')">

    <x-page-header :title="__('Add Training')" :description="__('Record a professional development activity for a staff member.')">
        <a href="{{ route('hr.training.index') }}" class="btn btn-secondary">{{ __('Back to training') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.training.store') }}">
            @csrf
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <x-select name="employee_id" :label="__('Employee')" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->full_name.' ('.$e->employee_id.')'])" placeholder="{{ __('Select employee…') }}" required />
                <x-input name="course_name" label="{{ __('Course / program') }}" placeholder="e.g. Classroom Management Workshop" required />
                <x-input name="provider" label="{{ __('Provider') }}" placeholder="e.g. MOE, ICT Academy" />
                <x-input name="hours" type="number" label="{{ __('Hours') }}" min="1" placeholder="e.g. 24" />
                <x-input name="trained_on" type="date" label="{{ __('Training date') }}" required />
                <x-input name="completed_on" type="date" label="{{ __('Completion date') }}" />
            </div>
            <x-textarea name="remarks" label="{{ __('Remarks') }}" :rows="3" placeholder="{{ __('Optional notes…') }}">{{ old('remarks') }}</x-textarea>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.training.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" class="icon-sm" />
                    {{ __('Add training') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>