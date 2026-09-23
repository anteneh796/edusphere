<x-layouts.app :title="__('Contract')">

    <x-page-header :title="$contract->contract_number"
        :description="__('Employment contract for :name', ['name' => $contract->employee->full_name])">
        <a href="{{ route('hr.contracts.edit', $contract) }}" class="btn btn-secondary">
            <x-icon name="pencil" class="icon-sm" />
            {{ __('Edit') }}
        </a>
    </x-page-header>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-3);">
        <x-card :title="__('Contract details')">
            <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap: var(--space-2);">
                <div class="text-sm"><span class="text-muted">{{ __('Number') }}:</span> {{ $contract->contract_number }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Employee') }}:</span> <a href="{{ route('hr.employees.show', $contract->employee) }}" class="link">{{ $contract->employee->full_name }}</a> ({{ $contract->employee->employee_id }})</div>
                <div class="text-sm"><span class="text-muted">{{ __('Type') }}:</span> {{ $contract->typeLabel() }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Renewal status') }}:</span> <x-badge :color="$contract->renewalBadgeColor()">{{ $contract->renewalLabel() }}</x-badge></div>
                <div class="text-sm"><span class="text-muted">{{ __('Start date') }}:</span> {{ $contract->start_date->format('M j, Y') }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('End date') }}:</span> {{ $contract->end_date?->format('M j, Y') ?? __('Open-ended') }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Position') }}:</span> {{ $contract->position?->name ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Department') }}:</span> {{ $contract->department?->name ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Salary grade') }}:</span> {{ $contract->salary_grade ?? '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Basic salary') }}:</span> {{ $contract->basic_salary ? number_format((float) $contract->basic_salary, 2).' ETB' : '—' }}</div>
                <div class="text-sm"><span class="text-muted">{{ __('Hours / week') }}:</span> {{ $contract->working_hours_per_week ?? '—' }}</div>
            </div>

            @if ($contract->notes)
                <hr style="border:none; border-top:1px solid var(--color-border); margin: var(--space-3) 0;" />
                <div class="text-sm"><span class="text-muted">{{ __('Notes') }}:</span><br />{{ $contract->notes }}</div>
            @endif
        </x-card>

        <x-card :title="__('Renewal')">
            <form method="POST" action="{{ route('hr.contracts.renew', $contract) }}" class="flex flex-col" style="gap: var(--space-2);">
                @csrf
                <x-select name="renewal_status" :label="__('Renewal status')" :options="collect(\App\Support\Enums\ContractRenewalStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$contract->renewal_status" />
                <x-input name="new_end_date" type="date" label="{{ __('New end date (if renewed)') }}" :hint="__('When renewed, the contract end date is updated and status returns to active.')" />
                <button type="submit" class="btn btn-primary">{{ __('Update renewal') }}</button>
            </form>
        </x-card>
    </div>

</x-layouts.app>