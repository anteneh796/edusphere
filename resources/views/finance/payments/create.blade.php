<x-layouts.app :title="__('Record payment')">
    <x-page-header
        :title="__('Record payment')"
        :description="__('Record a payment made against a student\u2019s account.')">
        <a href="{{ route('finance.payments.index') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to payments') }}
        </a>
    </x-page-header>

    <x-card>
        <form method="POST" action="{{ route('finance.payments.store') }}" class="grid gap-4">
            @csrf

            <x-select
                name="student_id"
                :label="__('Student')"
                :options="$students"
                placeholder="{{ __('Select a student…') }}"
                required />

            <x-select
                name="invoice_id"
                :label="__('Invoice (optional)')"
                :options="$invoices->mapWithKeys(fn ($i) => [$i->getKey() => $i->invoice_number.' — '.($i->student?->full_name ?? $i->getKey()).' ('.number_format((float) $i->balance(), 2).')'])"
                placeholder="{{ __('No invoice') }}" />

            <div class="grid grid-2 gap-4">
                <x-input name="amount" type="number" step="0.01" min="0.01" :label="__('Amount')" :value="old('amount')" required />
                <x-select
                    name="method"
                    :label="__('Method')"
                    :options="collect($methods)->mapWithKeys(fn ($method) => [$method->value => $method->label()])"
                    placeholder="{{ __('Select a method…') }}"
                    required />
            </div>

            <div class="grid grid-2 gap-4">
                <x-input name="status" type="hidden" value="confirmed" />
                <x-input name="reference" :label="__('Reference')" :value="old('reference')" hint="{{ __('Transaction or mobile money reference') }}" />
                <x-input name="provider" :label="__('Provider')" :value="old('provider')" placeholder="{{ __('e.g. bank name') }}" />
            </div>

            <ul class="text-xs text-light form-hint">
                <li>{{ __('Payments are recorded as confirmed by default.') }}</li>
            </ul>

            <div>
                <button type="submit" class="btn btn-primary">{{ __('Record payment') }}</button>
            </div>
        </form>
    </x-card>
</x-layouts.app>