<x-layouts.app :title="__('Contract Report')">

    <x-page-header :title="__('Contract Report')" :description="__('Active contracts expiring soon so renewals are planned in time.')">
        <a href="{{ route('hr.reports.index') }}" class="btn btn-secondary">{{ __('All reports') }}</a>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('hr.reports.contracts') }}" class="flex gap-1" style="align-items:flex-end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="within_days">{{ __('Expiring within') }}</label>
                <select id="within_days" name="within_days" class="form-select">
                    <option value="30" @selected($withinDays === 30)>30 {{ __('days') }}</option>
                    <option value="60" @selected($withinDays === 60)>60 {{ __('days') }}</option>
                    <option value="90" @selected($withinDays === 90)>90 {{ __('days') }}</option>
                    <option value="180" @selected($withinDays === 180)>180 {{ __('days') }}</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('View') }}</button>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Start date') }}</th>
                        <th>{{ __('End date') }}</th>
                        <th>{{ __('Days remaining') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        @php $daysLeft = now()->startOfDay()->diffInDays($contract->end_date->startOfDay(), false); @endphp
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $contract->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $contract->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $contract->position?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $contract->department?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $contract->start_date->format('M j, Y') }}</td>
                            <td class="text-sm">{{ $contract->end_date->format('M j, Y') }}</td>
                            <td>
                                @if ($daysLeft < 0)
                                    <x-badge color="danger">{{ __('Expired') }}</x-badge>
                                @elseif ($daysLeft <= 30)
                                    <x-badge color="warning">{{ $daysLeft.' '.__('days') }}</x-badge>
                                @else
                                    <x-badge color="success">{{ $daysLeft.' '.__('days') }}</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="file-text" :title="__('No contracts expiring')" :message="__('No active contracts expire within this window.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</x-layouts.app>