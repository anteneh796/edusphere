<x-layouts.app :title="__('Fees & billing')">
    <x-page-header
        :title="__('Fees & billing')"
        :description="$ward ? __('Fees for :name', ['name' => $ward->full_name]) : __('Fees & billing')">
        <x-parents.child-switcher :ward="$ward" :wards="$wards" />
    </x-page-header>

    @if (! $ward)
        <x-card>
            <x-empty-state icon="users" :title="__('No child selected')" :message="__('Link or select a child to view fees.')" />
        </x-card>
    @else
        <div class="grid grid-stats">
            <x-stat-card :label="__('Total billed')" :value="number_format($summary['billed'], 2)" icon="receipt" color="primary" />
            <x-stat-card :label="__('Total paid')" :value="number_format($summary['paid'], 2)" icon="check-circle" color="success" />
            <x-stat-card :label="__('Balance due')" :value="number_format($summary['balance'], 2)" icon="wallet" color="warning" />
        </div>

        <div class="grid grid-cols-1 gap-6 mt-4 lg:grid-cols-2" style="align-items:start;">
            <x-card :title="__('Invoices')">
                @forelse ($invoices as $invoice)
                    <div class="list-row">
                        <x-icon name="receipt" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $invoice->description }}</div>
                            <div class="text-xs text-light">
                                {{ $invoice->invoice_number }}
                                · {{ __('issued :date', ['date' => $invoice->issue_date?->format('d M Y') ?? '—']) }}
                                · {{ __('due :date', ['date' => $invoice->due_date?->format('d M Y') ?? '—']) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm-semibold">{{ number_format((float) $invoice->amount, 2) }}</div>
                            <span class="badge badge-{{ $invoice->status?->badgeColor() ?? 'neutral' }}">{{ $invoice->status?->label() ?? ucfirst($invoice->status ?? '') }}</span>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="receipt" :title="__('No invoices')" :message="__('Invoices for this child will appear here.')" />
                @endforelse
            </x-card>

            <x-card :title="__('Payments & receipts')">
                @forelse ($payments as $payment)
                    <div class="list-row">
                        <x-icon name="banknote" class="icon-sm text-light" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate">{{ $payment->payment_number }}</div>
                            <div class="text-xs text-light">
                                {{ $payment->paid_at?->format('d M Y') ?? '—' }}
                                · {{ $payment->method?->label() ?? ucfirst($payment->method ?? '') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm-semibold">{{ number_format((float) $payment->amount, 2) }}</div>
                            <a href="{{ route('cms.parent.receipts.show', $payment) }}" class="btn btn-ghost btn-sm">{{ __('Receipt') }}</a>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="banknote" :title="__('No payments yet')" :message="__('Confirmed payments and downloadable receipts will appear here.')" />
                @endforelse
            </x-card>
        </div>

        <p class="text-xs text-light mt-3">
            {{ __('Payments are recorded by the school finance officer. Contact the office to pay fees or for balance questions.') }}
        </p>
    @endif
</x-layouts.app>