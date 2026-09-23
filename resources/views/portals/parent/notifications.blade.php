<x-layouts.app :title="__('Notifications')">
    <x-page-header
        :title="__('Notifications')"
        :description="__(':count unread', ['count' => $unreadCount])">
        <div class="flex gap-2">
            <a href="{{ route('cms.parent.notifications', ['filter' => 'unread']) }}" class="btn btn-ghost">
                <x-icon name="bell" class="icon-sm" />
                {{ __('Unread only') }}
            </a>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('cms.parent.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost">
                        <x-icon name="check" class="icon-sm" />
                        {{ __('Mark all as read') }}
                    </button>
                </form>
            @endif
        </div>
    </x-page-header>

    <x-card>
        @forelse ($notifications as $notification)
            <a href="{{ route('cms.parent.notifications.show', $notification) }}" class="list-row @unless($notification->isRead()) bg-primary/5 @endunless" style="text-decoration:none;">
                <div class="stat-icon {{ $notification->priorityBadgeColor() ?? 'neutral' }}">
                    <x-icon :name="$notification->icon ?? 'bell'" class="icon-sm" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate font-medium">{{ $notification->title }}</div>
                    @if ($notification->body)
                        <div class="text-sm text-light line-clamp-2">{{ $notification->body }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xs text-light">{{ $notification->created_at?->diffForHumans() }}</div>
                    @unless ($notification->isRead())
                        <span class="dot mt-1 ml-auto"></span>
                    @endunless
                </div>
            </a>
        @empty
            <x-empty-state icon="bell" :title="__('No notifications')" :message="__('Updates from the school will appear here.')" />
        @endforelse

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </x-card>
</x-layouts.app>