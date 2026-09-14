<x-layouts.app title="Website CMS">
    <x-page-header title="Website CMS" description="Manage the public website content — pages, news and gallery. Changes appear on edusphere immediately.">
        <a href="{{ route('public.home') }}" class="btn btn-outline">
            <x-icon name="external" class="icon-sm" />
            View public site
        </a>
    </x-page-header>

    <div class="stat-grid">
        <x-stat-card label="Content pages" :value="$pages->count()" icon="file-text" href="{{ route('cms.pages.index') }}" />
        <x-stat-card label="Published news" :value="$publishedNews" icon="clipboard-check" href="{{ route('cms.news.index') }}" />
        <x-stat-card label="News items (incl. drafts)" :value="$totalNews" icon="clipboard" />
        <x-stat-card label="Gallery photos" :value="$totalGallery" icon="image" href="{{ route('cms.gallery.index') }}" />
    </div>

    <div class="grid-2" style="margin-top: var(--space-4);">
        <x-card title="Content pages" subtitle="Static website pages (about, academics, admissions, contact plus the home welcome text).">
            @forelse ($pages as $page)
                <div class="list-row">
                    <div style="display:flex; align-items:center; gap: var(--space-2); min-width:0;">
                        <x-icon name="file-text" class="icon-sm" style="color: var(--color-text-light);" />
                        <div style="min-width:0;">
                            <div class="truncate" style="font-weight: var(--weight-semibold);">{{ $page->title }}</div>
                            <div class="text-xs text-muted">/{{ $page->slug }} · {{ $page->deleted_at ? 'deleted' : ($page->published ? 'published' : 'draft') }}</div>
                        </div>
                    </div>
                    <a href="{{ route('cms.pages.edit', $page) }}" class="btn btn-ghost btn-sm">
                        <x-icon name="pencil" class="icon-sm" />
                        Edit
                    </a>
                </div>
            @empty
                <x-empty-state icon="file-text" title="No pages yet" message="Seed or create pages from the site content editor." />
            @endforelse
            <x-slot name="actions">
                <a href="{{ route('cms.pages.index') }}" class="btn btn-primary btn-sm">Manage pages</a>
            </x-slot>
        </x-card>

        <div style="display:grid; gap: var(--space-4); align-content:start;">
            <x-card title="News &amp; announcements" subtitle="{{ $publishedNews }} published of {{ $totalNews }} total.">
                <div style="display:flex; gap: var(--space-2); flex-wrap:wrap;">
                    <a href="{{ route('cms.news.create') }}" class="btn btn-primary btn-sm">
                        <x-icon name="plus" class="icon-sm" />
                        New news item
                    </a>
                    <a href="{{ route('cms.news.index') }}" class="btn btn-outline btn-sm">All items</a>
                </div>
            </x-card>

            <x-card title="Photo gallery" subtitle="{{ $publishedGallery }} photos are live on the public gallery.">
                <div style="display:flex; gap: var(--space-2); flex-wrap:wrap;">
                    <a href="{{ route('cms.gallery.create') }}" class="btn btn-primary btn-sm">
                        <x-icon name="plus" class="icon-sm" />
                        Add photo
                    </a>
                    <a href="{{ route('cms.gallery.index') }}" class="btn btn-outline btn-sm">All photos</a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>