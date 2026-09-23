<x-layouts.app :title="__('Receipt')">
    <x-page-header
        :title="__('Payment receipt')"
        :description="$ward ? __('Receipt for :name', ['name' => $ward->full_name]) : __('Payment receipt')">
        <a href="{{ route('cms.parent.billing') }}" class="btn btn-ghost">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to fees') }}
        </a>
    </x-page-header>

    <x-card>
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <div class="text-sm-semibold">{{ config('app.name') }}</div>
                <div class="text-xs text-light">{{ __('School Management System') }}</div>
            </div>
            <x-icon name="receipt" class="icon-lg text-light" />
        </div>

        <div class="border-t border-border pt-4">
            <div class="grid gap-3 text-sm md:grid-cols-2">
                <div>
                    <div class="text-xs text-light">{{ __('Payment number') }}</div>
                    <div class="font-medium">{{ $payment->payment_number }}</div>
                </div>
                <div>
                    <div class="text-xs text-light">{{ __('Date') }}</div>
                    <div class="font-medium">{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-light">{{ __('Student') }}</div>
                    <div class="font-medium">{{ $ward?->full_name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-light">{{ __('Student number') }}</div>
                    <div class="font-medium">{{ $ward?->student_number ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-light">{{ __('Payment method') }}</div>
                    <div class="font-medium">{{ $payment->method?->label() ?? ucfirst($payment->method ?? '—') }}</div>
                </div>
                <div>
                    <div class="text-xs text-light">{{ __('Reference') }}</div>
                    <div class="font-medium">{{ $payment->reference ?? '—' }}</div>
                </div>
                @if ($payment->invoice)
                    <div>
                        <div class="text-xs text-light">{{ __('Invoice') }}</div>
                        <div class="font-medium">{{ $payment->invoice->invoice_number }}</div>
                    </div>
                @endif
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-border pt-4">
                <span class="font-medium">{{ __('Amount paid') }}</span>
                <span class="text-lg font-semibold">{{ number_format((float) $payment->amount, 2) }}</span>
            </div>
        </div>

        <div class="mt-4">
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <x-icon name="printer" class="icon-sm" />
                {{ __('Print receipt') }}
            </button>
        </div>
    </x-card>
</x-layouts.app>