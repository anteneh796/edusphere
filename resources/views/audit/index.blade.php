<x-layouts.app :title="'Audit Log'">
    <x-breadcrumb :items="[['label' => 'Audit Log']]" />

    <x-page-header title="Audit Log" description="Review a trail of sensitive actions performed by staff." />

    <x-card>
        <form method="GET" action="{{ route('audit.index') }}" class="filter-grid">
            <x-input name="q" label="Search" :value="request('q')" placeholder="Action, module, record id or user…" wrapperClass="filter-q" />

            <div class="form-group">
                <label class="form-label" for="module-filter">Module</label>
                <select id="module-filter" name="module" class="form-select">
                    <option value="">All modules</option>
                    @foreach ($modules as $value => $label)
                        <option value="{{ $value }}" @selected(request('module') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-1" style="align-items:end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="search" class="icon-sm" />
                    Filter
                </button>
                @if (request('q') || request('module'))
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>IP address</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-sm">
                                <div>{{ $log->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-muted">{{ $log->created_at->format('g:i A') }}</div>
                            </td>
                            <td>
                                <div class="avatar-cell">
                                    <x-avatar :initials="optional($log->user)->initials() ?? '–'" size="sm" />
                                    <div style="min-width:0;">
                                        <div class="text-sm" style="font-weight: var(--weight-semibold);">{{ $log->user?->full_name ?? 'System' }}</div>
                                        <div class="text-xs text-muted">{{ $log->user?->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm">{{ $log->action }}</td>
                            <td>
                                <x-badge color="info">{{ str($log->module)->title() }}</x-badge>
                            </td>
                            <td class="text-sm text-muted">{{ $log->ip_address ?? '—' }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('audit.show', $log) }}" class="btn btn-ghost btn-sm btn-icon" title="View details">
                                    <x-icon name="eye" class="icon-sm" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="shield-check" title="No audit entries found" message="Try adjusting your filters." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </x-card>

</x-layouts.app>