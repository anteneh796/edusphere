<x-layouts.app :title="__('Notifications')">

    <x-page-header :title="__('Notifications')" :description="__('Alerts, approvals and system updates addressed to you.')">
        @if ($unreadCount)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    <x-icon name="check" class="icon-sm" />
                    {{ __('Mark all as read') }}
                </button>
            </form>
        @endif
    </x-page-header>

    <x-card>
        <div class="flex gap-2" style="padding: var(--space-2) var(--space-3); border-bottom: 1px solid var(--color-border);">
            @foreach ([
                '' => __('All'),
                'unread' => __('Unread'),
                'read' => __('Read'),
            ] as $value => $label)
                <a href="{{ route('notifications.index', $value ? ['filter' => $value] : []) }}"
                    class="badge {{ (request('filter') ?: '') === $value ? 'badge-primary' : 'badge-neutral' }}"
                    style="text-decoration:none;">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col">
            @forelse ($notifications as $notification)
                <div class="notification-row flex" style="gap: var(--space-3); padding: var(--space-3); align-items:flex-start;
                    {{ $notification->isRead() ? '' : 'background: color-mix(in srgb, var(--primary) 6%, transparent);' }}">
                    <div class="stat-icon {{ $notification->isRead() ? 'muted' : 'primary' }}">
                        <x-icon name="{{ $notification->icon ?? 'bell' }}" class="icon-md" />
                    </div>
                    <div style="min-width:0; flex:1;">
                        <div style="display:flex; gap: var(--space-2); align-items:center;">
                            <span style="font-weight: var(--weight-semibold);">{{ $notification->title }}</span>
                            <x-badge :color="$notification->priorityBadgeColor()">{{ \Illuminate\Support\Str::headline($notification->priority) }}</x-badge>
                            @if (! $notification->isRead())
                                <span class="badge-dot" style="color: var(--primary);"></span>
                            @endif
                        </div>
                        @if ($notification->body)
                            <div class="text-sm text-muted" style="margin-top: 2px;">{{ $notification->body }}</div>
                        @endif
                        <div class="text-xs text-muted" style="margin-top: 4px;">
                            {{ $notification->created_at->diffForHumans() }}
                            <span>·</span>
                            {{ $notification->categoryLabel() }}
                        </div>
                    </div>
                    <a href="{{ route('notifications.show', $notification) }}" class="btn btn-ghost btn-sm btn-icon" :title="__('Open')">
                        <x-icon name="arrow-right" class="icon-sm" />
                    </a>
                </div>
            @empty
                <div style="padding: var(--space-4);">
                    <x-empty-state icon="bell" :title="__('No notifications')" :message="__('You are all caught up.')" />
                </div>
            @endforelse
        </div>

        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
    </x-card>

</x-layouts.app>