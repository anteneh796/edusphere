<x-layouts.app :title="__('Payments')">
    <x-page-header
        :title="__('Payments')"
        :description="__('Payments recorded against student invoices.')">
        <a href="{{ route('finance.payments.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            {{ __('Record payment') }}
        </a>
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-right">{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="font-medium">{{ $payment->payment_number }}</td>
                            <td>{{ $payment->student?->full_name ?? '—' }}</td>
                            <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                            <td>{{ $payment->method?->label() ?? ucfirst($payment->method ?? '—') }}</td>
                            <td class="text-right">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->paid_at?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                <span class="badge badge-{{ $payment->status?->badgeColor() ?? 'neutral' }}">{{ $payment->status?->label() ?? ucfirst($payment->status ?? '') }}</span>
                            </td>
                            <td class="text-right">
                                @if ($payment->status?->value === 'pending')
                                    <form method="POST" action="{{ route('finance.payments.confirm', $payment) }}" onsubmit="return confirm('{{ __('Confirm this payment?') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Confirm') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="banknote" :title="__('No payments yet')" :message="__('Record the first payment to get started.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>