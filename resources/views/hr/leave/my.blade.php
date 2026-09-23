<x-layouts.app :title="__('My Leave')">

    <x-page-header :title="__('My Leave')" :description="__('Your leave requests and their review status.')">
        @if (auth()->user()->hasPermission('hr.create') && $employee)
            <a href="{{ route('hr.leave.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Request leave') }}
            </a>
        @endif
    </x-page-header>

    @if (! $employee)
        <x-card>
            <x-empty-state icon="user-x" :title="__('No employee profile found')"
                :message="__('Your account is not linked to an employee record yet. Contact the HR manager to link your account so you can request leave.')" />
        </x-card>
    @else
        <x-card :title="__('Leave allowance')">
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: var(--space-2);">
                @foreach (\App\Domains\HumanResources\Models\LeaveType::where('is_active', true)->get() as $leaveType)
                    @php
                        $used = $employee->leaveRequests()
                            ->where('leave_type_id', $leaveType->id)
                            ->whereIn('status', [\App\Support\Enums\LeaveRequestStatus::Approved->value, \App\Support\Enums\LeaveRequestStatus::Pending->value])
                            ->whereYear('start_date', now()->year)
                            ->sum('days');
                    @endphp
                    <div class="stat-card">
                        <div class="stat-icon primary"><x-icon name="calendar" class="icon-lg" /></div>
                        <div style="min-width:0;">
                            <div class="stat-value">{{ $used }} / {{ $leaveType->days_per_year }}</div>
                            <div class="stat-label">{{ $leaveType->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card :title="__('My requests')">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th>{{ __('Days') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td class="text-sm">{{ $request->leaveType->name }}</td>
                                <td class="text-sm">{{ $request->start_date->format('M j, Y') }} – {{ $request->end_date->format('M j, Y') }}</td>
                                <td class="text-sm">{{ $request->days }}</td>
                                <td>
                                    <x-badge :color="$request->statusBadgeColor()" :dot="true">{{ $request->statusLabel() }}</x-badge>
                                </td>
                                <td class="actions-cell">
                                    <a href="{{ route('hr.leave.show', $request) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('View') }}">
                                        <x-icon name="eye" class="icon-sm" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state icon="calendar" :title="__('No leave requests yet')" :message="__('Submit a leave request when you need time off.')">
                                        <a href="{{ route('hr.leave.create') }}" class="btn btn-primary btn-sm">{{ __('Request leave') }}</a>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $requests->links() }}</div>
        </x-card>
    @endif

</x-layouts.app>