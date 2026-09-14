<x-layouts.app title="Photo Gallery">
    <x-page-header
        title="Photo Gallery"
        description="Manage the gallery items shown on the public website.">
        <a href="{{ route('cms.gallery.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            Add photo
        </a>
    </x-page-header>

    <x-card title="Gallery items">
        @forelse ($items as $item)
            <div class="list-row">
                <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                    @if ($item->image_url)
                        <img src="{{ $item->image_url }}" alt="" class="thumb thumb-sm">
                    @else
                        <x-icon name="image" class="icon-lg text-muted" />
                    @endif
                    <div style="min-width:0;">
                        <div class="truncate">{{ $item->caption ?? $item->title ?? 'Untitled' }}</div>
                        @if ($item->title)
                            <div class="text-xs text-light">{{ $item->title }}</div>
                        @endif
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap: var(--space-1);">
                    <span class="badge {{ $item->published ? 'badge-success' : 'badge-warning' }}">
                        {{ $item->published ? 'published' : 'draft' }}
                    </span>
                    <a href="{{ route('cms.public.gallery') }}" target="_blank" class="btn btn-ghost btn-sm" title="View site">
                        <x-icon name="external" class="icon-sm" />
                    </a>
                    <a href="{{ route('cms.gallery.edit', $item) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        Edit
                    </a>
                    <form method="POST" action="{{ route('cms.gallery.destroy', $item) }}" onsubmit="return confirm('Delete this item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost-danger btn-sm">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state icon="image" title="No gallery items" message="Add photos to showcase your school on the public website." />
        @endforelse

        @if (method_exists($items, 'links'))
            {{ $items->links() }}
        @endif
    </x-card>
</x-layouts.app>
