<x-layouts.app :title="__('Events')">
    <x-page-header
        :title="__('Events')"
        :description="__('Manage the events shown on the public website calendar.')">
        <a href="{{ route('cms.events.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            {{ __('New event') }}
        </a>
    </x-page-header>

    <x-card :title="__('School events')">
        <x-slot name="actions">
            <form method="GET" action="{{ route('cms.events.index') }}" class="field-filter">
                <x-input name="q" :placeholder="__('Search events…')" :value="request('q')" class="input-sm" />
            </form>
        </x-slot>

        @forelse ($items as $item)
            <div class="list-row">
                <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                    <div style="min-width:0;">
                        <div class="truncate">
                            @if ($item->featured)
                                <x-icon name="star" class="icon-sm" style="color: var(--color-warning);" />
                            @endif
                            {{ $item->title }}
                        </div>
                        <div class="text-xs text-light">
                            {{ optional($item->starts_at)->format('M j, Y · g:i A') ?? __('To be announced') }}
                            @if ($item->location)
                                · {{ $item->location }}
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap: var(--space-1);">
                    <span class="badge {{ $item->published ? 'badge-success' : 'badge-warning' }}">
                        {{ $item->published ? __('published') : __('draft') }}
                    </span>
                    <a href="{{ route('cms.events.edit', $item) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        {{ __('Edit') }}
                    </a>
                    <form method="POST" action="{{ route('cms.events.destroy', $item) }}" onsubmit="return confirm(@js(__('Delete this event? This cannot be undone.')));">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost-danger btn-sm">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state icon="calendar" :title="__('No events')" :message="__('Create your first event to publish it on the public calendar.')" />
        @endforelse

        @if (method_exists($items, 'links'))
            {{ $items->links() }}
        @endif
    </x-card>
</x-layouts.app>