<x-layouts.app :title="__('Leave Types')">

    <x-page-header :title="__('Leave Types')" :description="__('Configure what leave categories staff can request and their annual entitlement.')">
        @if (auth()->user()->hasPermission('hr.create'))
            <a href="{{ route('hr.leave-types.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('Add leave type') }}
            </a>
        @endif
    </x-page-header>

    <x-card>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Leave type') }}</th>
                        <th>{{ __('Days / year') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Requests this year') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th width="30%">{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveTypes as $leaveType)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $leaveType->name }}</div>
                                <div class="text-xs text-muted">{{ $leaveType->code }}</div>
                            </td>
                            <td class="text-sm">{{ $leaveType->days_per_year }}</td>
                            <td>
                                <x-badge :color="$leaveType->is_paid ? 'success' : 'neutral'">{{ $leaveType->is_paid ? __('Paid') : __('Unpaid') }}</x-badge>
                            </td>
                            <td class="text-sm">
                                {{ $leaveType->leave_requests_count }}
                                <span class="text-xs text-muted">({{ $leaveType->approved_count }} approved)</span>
                            </td>
                            <td>
                                <x-badge :color="$leaveType->is_active ? 'success' : 'danger'" :dot="true">{{ $leaveType->is_active ? __('Active') : __('Inactive') }}</x-badge>
                            </td>
                            <td class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($leaveType->description, 70) ?? '—' }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.leave-types.edit', $leaveType) }}" class="btn btn-ghost btn-sm btn-icon" title="{{ __('Edit') }}">
                                    <x-icon name="pencil" class="icon-sm" />
                                </a>
                                @if (auth()->user()->hasPermission('hr.delete'))
                                    <form method="POST" action="{{ route('hr.leave-types.destroy', $leaveType) }}" class="inline" onsubmit="return confirm('{{ __('Delete this leave type?') }}')">
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
                                <x-empty-state icon="calendar" :title="__('No leave types yet')" :message="__('Leave types define what staff can request, e.g. Annual, Sick or Maternity leave.')">
                                    <a href="{{ route('hr.leave-types.create') }}" class="btn btn-primary btn-sm">{{ __('Add leave type') }}</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $leaveTypes->links() }}</div>
    </x-card>

</x-layouts.app>