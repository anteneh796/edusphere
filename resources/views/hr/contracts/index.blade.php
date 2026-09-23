<x-layouts.app :title="__('Contracts')">

    <x-page-header :title="__('Contracts')" :description="__('Employment contracts, renewal status and expiry tracking.')">
        @if (auth()->user()->hasPermission('hr.payroll'))
            <a href="{{ route('hr.contracts.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New contract') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.contracts.index') }}" id="contract-filters" class="grid-3" style="display:grid; grid-template-columns: 1fr 1fr 2fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="renewal_status">{{ __('Renewal status') }}</label>
                <select id="renewal_status" name="renewal_status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\ContractRenewalStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('renewal_status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="employment_type">{{ __('Employment type') }}</label>
                <select id="employment_type" name="employment_type" class="form-select">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (\App\Support\Enums\EmploymentType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('employment_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1" style="align-items:flex-end;">
                <button type="submit" class="btn btn-primary" form="contract-filters">{{ __('Filter') }}</button>
                @if (request()->hasAny(['renewal_status', 'employment_type']))
                    <a href="{{ route('hr.contracts.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Renewal') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr>
                            <td>
                                <a href="{{ route('hr.contracts.show', $contract) }}" class="link">{{ $contract->contract_number }}</a>
                            </td>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $contract->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $contract->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $contract->position?->name ?? '—' }}</td>
                            <td class="text-sm">
                                {{ $contract->start_date->format('M j, Y') }}
                                @if ($contract->end_date)
                                    –<br /><span class="text-muted">{{ $contract->end_date->format('M j, Y') }}</span>
                                @else
                                    {{ __('· Open-ended') }}
                                @endif
                            </td>
                            <td class="text-sm">{{ \App\Support\Enums\EmploymentType::from($contract->employment_type)->label() }}</td>
                            <td>
                                <x-badge :color="$contract->renewalBadgeColor()">{{ $contract->renewalLabel() }}</x-badge>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.contracts.show', $contract) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                                <a href="{{ route('hr.contracts.edit', $contract) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                    <x-icon name="pencil" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.contracts.destroy', $contract) }}" class="inline" onsubmit="return confirm('{{ __('Delete this contract?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--color-danger);" title="{{ __('Delete') }}">
                                            <x-icon name="trash" class="icon-sm" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="briefcase" :title="__('No contracts found')" :message="__('Create a contract to track employment terms and renewals.')">
                                    <a href="{{ route('hr.contracts.create') }}" class="btn btn-primary btn-sm">{{ __('New contract') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $contracts->links() }}</div>
    </x-card>

</x-layouts.app>