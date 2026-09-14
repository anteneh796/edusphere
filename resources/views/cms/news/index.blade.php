<x-layouts.app title="News &amp; Events">
    <x-page-header
        title="News &amp; Events"
        description="Manage the news posts that appear on the public website.">
        <a href="{{ route('cms.news.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            New post
        </a>
    </x-page-header>

    <x-card title="News posts">
        <x-slot name="actions">
            <form method="GET" action="{{ route('cms.news.index') }}" class="field-filter">
                <x-input name="q" placeholder="Search news…" :value="request('q')" class="input-sm" />
            </form>
        </x-slot>

        @forelse ($items as $item)
            <div class="list-row">
                <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                    @if ($item->featured_image_url)
                        <img src="{{ $item->featured_image_url }}" alt="" class="thumb thumb-sm">
                    @else
                        <x-icon name="newspaper" class="icon-lg text-muted" />
                    @endif
                    <div style="min-width:0;">
                        <div class="truncate">{{ $item->title }}</div>
                        <div class="text-xs text-light">
                            {{ optional($item->published_at)->diffForHumans() ?? 'Unpublished' }}
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap: var(--space-1);">
                    <span class="badge {{ $item->published ? 'badge-success' : 'badge-warning' }}">
                        {{ $item->published ? 'published' : 'draft' }}
                    </span>
                    <a href="{{ route('cms.public.news-show', $item) }}" target="_blank" class="btn btn-ghost btn-sm" title="View">
                        <x-icon name="external" class="icon-sm" />
                    </a>
                    <a href="{{ route('cms.news.edit', $item) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        Edit
                    </a>
                    <form method="POST" action="{{ route('cms.news.destroy', $item) }}" onsubmit="return confirm('Delete this post? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost-danger btn-sm">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state icon="newspaper" title="No news posts" message="Create your first post to share updates on the public website." />
        @endforelse

        @if (method_exists($items, 'links'))
            {{ $items->links() }}
        @endif
    </x-card>
</x-layouts.app>
