<x-layouts.app :title="'Audit entry'">
    <x-breadcrumb :items="[
        ['label' => 'Audit Log', 'url' => route('audit.index')],
        ['label' => 'Entry'],
    ]" />

    <x-page-header title="Audit entry" description="Full details of a recorded activity event.">
        <a href="{{ route('audit.index') }}" class="btn btn-secondary">
            <x-icon name="chevron-left" class="icon-sm" />
            Back to log
        </a>
    </x-page-header>

    <div style="max-width: 900px;">
        <x-card title="Event details">
            <dl class="detail-list">
                <div><dt>Recorded at</dt><dd>{{ $auditLog->created_at->format('M j, Y · g:i:s A') }}</dd></div>
                <div><dt>User</dt><dd>{{ $auditLog->user?->full_name ?? 'System' }} <span class="text-muted">({{ $auditLog->user?->email ?? 'no account' }})</span></dd></div>
                <div><dt>Action</dt><dd>{{ $auditLog->action }}</dd></div>
                <div><dt>Module</dt><dd>{{ str($auditLog->module)->title() }}</dd></div>
                <div><dt>Record ID</dt><dd>{{ $auditLog->record_id ?? '—' }}</dd></div>
                <div><dt>IP address</dt><dd>{{ $auditLog->ip_address ?? '—' }}</dd></div>
                <div><dt>User agent</dt><dd>{{ $auditLog->user_agent ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Metadata">
            @if (filled($auditLog->meta))
                <pre class="code-block">{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <x-empty-state icon="file-text" title="No metadata" message="This event did not carry additional data." />
            @endif
        </x-card>
    </div>

</x-layouts.app>