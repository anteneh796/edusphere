<x-layouts.app :title="__('Approvals')">

    <x-page-header :title="__('Approvals')" :description="__('Review and track requests that need executive sign-off.')">
        @can('create', App\Domains\Approvals\Models\ApprovalRequest::class)
            <a href="{{ route('approvals.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="icon-sm" />
                {{ __('New request') }}
            </a>
        @endcan
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('approvals.index') }}" id="approval-filters" class="grid-3" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-2); align-items:end; padding: var(--space-3);">
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="type-filter">{{ __('Type') }}</label>
                <select id="type-filter" name="type" class="form-select">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (\App\Support\Enums\ApprovalType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" for="status-filter">{{ __('Status') }}</label>
                <select id="status-filter" name="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (\App\Support\Enums\ApprovalStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    {{ __('Filter') }}
                </button>
                @if (request('type') || request('status'))
                    <a href="{{ route('approvals.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <div class="table-responsive" style="border-top:1px solid var(--color-border);">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Requested by') }}</th>
                        <th>{{ __('Reviewer') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $approval)
                        <tr>
                            <td>
                                <div style="font-weight: var(--weight-semibold);">{{ $approval->typeLabel() }}</div>
                                <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($approval->reason ?? '', 60) }}</div>
                            </td>
                            <td class="text-sm">
                                <div>{{ $approval->requester?->full_name ?? '—' }}</div>
                            </td>
                            <td class="text-sm text-muted">
                                @if ($approval->reviewed_by_id)
                                    {{ $approval->reviewer?->full_name }}
                                @else
                                    {{ $approval->reviewerTitle() }}
                                @endif
                            </td>
                            <td class="text-xs text-muted">{{ $approval->submitted_at?->format('M j, Y H:i') }}</td>
                            <td>
                                <x-badge :color="$approval->statusBadgeColor()" :dot="$approval->isPending()">
                                    {{ $approval->statusEnum()?->label() ?? $approval->status }}
                                </x-badge>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('approvals.show', $approval) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('View')">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="file-text" :title="__('No approval requests')" :message="__('Requests submitted for sign-off will appear here.')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $requests->links() }}
        </div>
    </x-card>

</x-layouts.app>