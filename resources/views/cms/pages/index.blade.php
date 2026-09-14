<x-layouts.app title="Website pages">
    <x-page-header
        title="Website pages"
        description="Manage the content shown on the public website. Pages are served at their own URLs (e.g. /about) and the home welcome text.">
        <a href="{{ route('public.home') }}" class="btn btn-outline">
            <x-icon name="external" class="icon-sm" />
            View public site
        </a>
    </x-page-header>

    <x-card title="Pages" subtitle="System pages — update their copy, then save.">
        <x-slot name="actions">
            <div class="field-filter">
                <form method="GET" action="{{ route('cms.pages.index') }}">
                    <x-input name="q" placeholder="Search pages…" :value="request('q')" class="input-sm" style="width:200px;" />
                </form>
            </div>
        </x-slot>

        @forelse ($pages as $page)
            <div class="list-row">
                <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0; flex:1;">
                    <x-icon name="file-text" class="icon-sm text-muted" />
                    <div style="min-width:0;">
                        <div class="truncate">{{ $page->title }}</div>
                        <div class="text-xs text-light">/{{ $page->slug }} · {{ $page->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap: var(--space-1);">
                    @if ($page->deleted_at)
                        <span class="badge badge-neutral">deleted</span>
                    @else
                        <span class="badge {{ $page->published ? 'badge-success' : 'badge-warning' }}">{{ $page->published ? 'published' : 'draft' }}</span>
                    @endif
                    <a href="{{ $page->url() }}" target="_blank" class="btn btn-ghost btn-sm" title="Preview">
                        <x-icon name="eye" class="icon-sm" />
                    </a>
                    <a href="{{ route('cms.pages.edit', $page) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        Edit
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <x-icon name="file-text" class="icon-lg" />
                <h3>No pages yet</h3>
                <p>Pages are created by the seeding process. Run <code>php artisan db:seed</code> to install the default content.</p>
            </div>
        @endforelse
    </x-card>
</x-layouts.app>