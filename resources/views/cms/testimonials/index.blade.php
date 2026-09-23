<x-layouts.app :title="__('Testimonials')">
    <x-page-header
        :title="__('Testimonials')"
        :description="__('Manage the parent and alumni testimonials shown on the home page.')">
        <a href="{{ route('cms.testimonials.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="icon-sm" />
            {{ __('New testimonial') }}
        </a>
    </x-page-header>

    <x-card :title="__('Testimonials')">
        @forelse ($items as $item)
            <div class="list-row">
                <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                    @if ($item->avatar_url)
                        <img src="{{ $item->avatar_url }}" alt="" class="thumb thumb-sm">
                    @else
                        <x-icon name="user" class="icon-lg text-muted" />
                    @endif
                    <div style="min-width:0;">
                        <div class="truncate">{{ $item->name }}</div>
                        <div class="text-xs text-light">{{ $item->role ?? __('Parent / Guardian') }}</div>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap: var(--space-1);">
                    <span class="badge {{ $item->published ? 'badge-success' : 'badge-warning' }}">
                        {{ $item->published ? __('published') : __('draft') }}
                    </span>
                    <a href="{{ route('cms.testimonials.edit', $item) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        {{ __('Edit') }}
                    </a>
                    <form method="POST" action="{{ route('cms.testimonials.destroy', $item) }}" onsubmit="return confirm(@js(__('Delete this testimonial?')));">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost-danger btn-sm">
                            <x-icon name="trash" class="icon-sm" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state icon="quote" :title="__('No testimonials')" :message="__('Add testimonials to share kind words from families on the public website.')" />
        @endforelse
    </x-card>
</x-layouts.app>