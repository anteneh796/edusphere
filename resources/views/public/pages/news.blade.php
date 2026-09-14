<x-layouts.public title="News &amp; Events">
    <div class="public-page-head">
        <h1>News &amp; Events</h1>
        <p>Official announcements, events and school life updates.</p>
    </div>

    @if ($items->isEmpty())
        <x-empty-state icon="newspaper" title="No news yet" message="Check back soon for the latest updates from {{ \App\Domains\Settings\Models\Setting::schoolName() }}." />
    @else
        <div class="public-grid news-grid">
            @foreach ($items as $item)
                <a href="{{ route('cms.public.news-show', $item) }}" class="news-card">
                    @if ($item->featured_image_url)
                        <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" class="news-card-image">
                    @endif
                    <div class="news-card-body">
                        <div class="text-xs text-light">
                            {{ optional($item->published_at)->format('M j, Y') ?? optional($item->created_at)->format('M j, Y') }}
                        </div>
                        <h3 class="news-card-title">{{ $item->title }}</h3>
                        @if ($item->excerpt)
                            <p class="news-card-excerpt">{{ $item->excerpt }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if (method_exists($items, 'links'))
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif
    @endif
</x-layouts.public>
