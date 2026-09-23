<x-layouts.app :title="__('Invoices')">
    <x-page-header
        :title="__('Invoices')"
        :description="__('All invoices issued to students.')">
        <a href="{{ route('finance.invoices.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            {{ __('New invoice') }}
        </a>
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th class="text-right">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="font-medium">{{ $invoice->invoice_number }}</td>
                            <td>
                                {{ $invoice->student?->full_name ?? '—' }}
                                @if ($invoice->student?->classRoom?->gradeLevel?->name)
                                    <span class="text-xs text-light">· {{ $invoice->student->classRoom->gradeLevel->name }}</span>
                                @endif
                            </td>
                            <td>{{ $invoice->description }}</td>
                            <td class="text-right">{{ number_format((float) $invoice->amount, 2) }}</td>
                            <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                <span class="badge badge-{{ $invoice->status?->badgeColor() ?? 'neutral' }}">{{ $invoice->status?->label() ?? ucfirst($invoice->status ?? '') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="receipt" :title="__('No invoices yet')" :message="__('Create the first invoice to get started.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>