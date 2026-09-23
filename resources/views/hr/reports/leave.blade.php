<x-layouts.app :title="__('Leave Report')">

    <x-page-header :title="__('Leave Report')" :description="__('Approved leave within a period, broken down by type.')">
        <a href="{{ route('hr.reports.index') }}" class="btn btn-secondary">{{ __('All reports') }}</a>
    </x-page-header>

    @if ($requests->isNotEmpty())
        <x-card :title="__('Days approved by leave type')">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        @foreach ($byType as $type => $days)
                            <tr>
                                <td>{{ $type }}</td>
                                <td class="text-right" style="width:60%;">
                                    <div style="display:flex; align-items:center; gap: var(--space-2);">
                                        <div style="flex:1; height:8px; border-radius:999px; background: var(--color-border); overflow:hidden;">
                                            <div style="width: {{ $byType->isNotEmpty() ? round(($days / $byType->sum()) * 100) : 0 }}%; height:100%; background: var(--color-info);"></div>
                                        </div>
                                        <span style="font-weight: var(--weight-semibold); width:4ch; text-align:right;">{{ $days }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <tr style="border-top:2px solid var(--color-border);">
                            <td style="font-weight: var(--weight-semibold);">{{ __('Total') }}</td>
                            <td class="text-right" style="font-weight: var(--weight-semibold);">{{ $byType->sum() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    <x-card style="margin-top: var(--space-3);">
        <form method="GET" action="{{ route('hr.reports.leave') }}" class="grid gap-1" style="grid-template-columns: 1fr 1fr auto auto; align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="from">{{ __('From') }}</label>
                <input type="date" id="from" name="from" value="{{ $from }}" class="form-control" />
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="to">{{ __('To') }}</label>
                <input type="date" id="to" name="to" value="{{ $to }}" class="form-control" />
            </div>
            <button type="submit" class="btn btn-primary">{{ __('View') }}</button>
            <a href="{{ route('hr.reports.leave') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Days') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $request->employee->full_name }}</div>
                                <div class="text-xs text-muted">{{ $request->employee->employee_id }}</div>
                            </td>
                            <td class="text-sm">{{ $request->leaveType?->name ?? '—' }}</td>
                            <td class="text-sm">{{ $request->start_date->format('M j') }} – {{ $request->end_date->format('M j, Y') }}</td>
                            <td class="text-sm">{{ $request->days }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="briefcase" :title="__('No approved leave')" :message="__('No approved leave requests match this period.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</x-layouts.app>