<x-layouts.app :title="__('Issue Letter')">

    <x-page-header :title="__('Issue Official Letter')" :description="__('Create an official HR letter for a staff member.')">
        <a href="{{ route('hr.letters.index') }}" class="btn btn-secondary">{{ __('Back to letters') }}</a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('hr.letters.store') }}">
            @csrf
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <x-select name="employee_id" :label="__('Employee')" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->full_name.' ('.$e->employee_id.')'])" placeholder="{{ __('Select employee…') }}" required />
                <x-select name="letter_type" :label="__('Letter type')"
                    :options="collect(\App\Support\Enums\OfficialLetterType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                    placeholder="{{ __('Select…') }}" required />
                <x-input name="title" label="{{ __('Title / subject') }}" placeholder="e.g. Appointment to Mathematics Teacher" required style="grid-column: 1 / -1;" />
                <x-input name="reference_number" label="{{ __('Reference number') }}" placeholder="{{ $nextReference }} — {{ __('leave blank to auto-generate') }}" />
                <x-input name="issued_on" type="date" label="{{ __('Issue date') }}" :value="old('issued_on', now()->toDateString())" required />
            </div>
            <x-textarea name="content" label="{{ __('Letter body') }}" :rows="8" placeholder="{{ __('Write the letter content in full…') }}" required>{{ old('content') }}</x-textarea>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
            <div class="flex" style="justify-content:flex-end; gap: var(--space-1);">
                <a href="{{ route('hr.letters.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="file-text" class="icon-sm" />
                    {{ __('Issue letter') }}
                </button>
            </div>
        </form>
    </x-card>

</x-layouts.app>