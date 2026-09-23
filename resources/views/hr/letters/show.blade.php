<x-layouts.app :title="__('Official Letter')">

    <x-page-header :title="__('Official Letter')"
        :description="__(':employee · :reference', ['employee' => $letter->employee->full_name, 'reference' => $letter->reference_number])">
        <a href="{{ route('hr.letters.index') }}" class="btn btn-secondary">{{ __('Back to letters') }}</a>
    </x-page-header>

    <div class="card" style="max-width: 820px; margin: 0 auto;">
        <div style="padding: var(--space-4);">
            <div class="flex" style="justify-content:space-between; align-items:flex-start; gap: var(--space-2); padding-bottom: var(--space-3); border-bottom: 2px solid var(--color-border);">
                <div>
                    <div style="font-size: var(--font-lg); font-weight: var(--weight-bold);">{{ config('app.name') }}</div>
                    <div class="text-sm text-muted">{{ __('Human Resource Department') }}</div>
                </div>
                <div style="text-align:right;">
                    <div class="text-sm"><strong>{{ __('Reference') }}:</strong> {{ $letter->reference_number }}</div>
                    <div class="text-sm"><strong>{{ __('Date') }}:</strong> {{ $letter->issued_on?->format('F j, Y') }}</div>
                </div>
            </div>

            <div style="padding: var(--space-3) 0;">
                <div style="font-weight: var(--weight-semibold); margin-bottom: var(--space-1); text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-muted); font-size: var(--font-sm);">
                    <x-badge color="primary">{{ $letter->typeEnum()?->label() ?? $letter->letter_type }}</x-badge>
                </div>
                <h1 style="font-size: var(--font-xl); font-weight: var(--weight-bold); margin: 0 0 var(--space-2);">{{ $letter->title }}</h1>

                <div class="flex flex-col" style="gap: var(--space-1); margin-bottom: var(--space-3); font-size: var(--font-sm);">
                    <div><strong>{{ __('Employee') }}:</strong> {{ $letter->employee->full_name }}</div>
                    <div><strong>{{ __('Employee ID') }}:</strong> {{ $letter->employee->employee_id }}</div>
                    <div><strong>{{ __('Position') }}:</strong> {{ $letter->employee?->position?->name ?? '—' }}</div>
                </div>

                <article style="line-height: 1.7; white-space: pre-wrap;">{{ $letter->content }}</article>

                @if ($letter->issuedBy)
                    <div style="margin-top: var(--space-4);">
                        <div style="border-top:1px solid var(--color-border); width: 260px; padding-top: var(--space-1);">
                            <div style="font-weight: var(--weight-semibold);">{{ $letter->issuedBy->first_name.' '.$letter->issuedBy->last_name }}</div>
                            <div class="text-sm text-muted">{{ __('Signature · HR Department') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer flex" style="justify-content:flex-end; gap: var(--space-1);">
            @if (auth()->user()->hasPermission('hr.delete'))
                <form method="POST" action="{{ route('hr.letters.destroy', $letter) }}" class="inline" onsubmit="return confirm('{{ __('Delete this letter?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            @endif
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <x-icon name="printer" class="icon-sm" />
                {{ __('Print') }}
            </button>
        </div>
    </div>

</x-layouts.app>