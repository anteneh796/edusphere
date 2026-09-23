<x-layouts.app :title="__('New invoice')">
    <x-page-header
        :title="__('New invoice')"
        :description="__('Issue an invoice to a student.')">
        <a href="{{ route('finance.invoices.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to invoices') }}
        </a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('finance.invoices.store') }}" class="grid gap-4">
            @csrf

            <x-select
                name="student_id"
                :label="__('Student')"
                :options="$students"
                placeholder="{{ __('Select a student…') }}"
                required />

            <x-input name="description" :label="__('Description')" :value="old('description')" placeholder="{{ __('e.g. Term 1 tuition fees') }}" required />

            <div class="grid grid-2 gap-4">
                <x-input name="amount" type="number" step="0.01" min="0.01" :label="__('Amount')" :value="old('amount')" required />
                <x-input name="issue_date" type="date" :label="__('Issue date')" :value="old('issue_date', now()->format('Y-m-d'))" required />
            </div>

            <div class="grid grid-2 gap-4">
                <x-input name="due_date" type="date" :label="__('Due date')" :value="old('due_date')" />
                <x-input name="notes" :label="__('Notes')" :value="old('notes')" />
            </div>

            <div>
                <button type="submit" class="btn btn-primary">{{ __('Create invoice') }}</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>