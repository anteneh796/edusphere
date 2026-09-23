<x-layouts.public :title="__('News & Stories')"
    :description="__('Official announcements, events and stories from school life.')"
>
    @php
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('news');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', \App\Domains\Settings\Models\Setting::schoolName(), $v);
        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('From the campus') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('News & Stories') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('Official announcements, event recaps and the everyday stories that make our community special.') !!}</p>
        </div>
    </section>

    <section class="public-section">
        @if ($items->isEmpty())
            <p class="public-empty">{{ __('Check back soon for the latest updates from the school.') }}</p>
        @else
            <div class="public-grid news-grid">
                @foreach ($items as $item)
                    <a href="{{ route('public.news-show', $item) }}" class="news-card">
                        <div class="news-card-media">
                            @if ($item->featured_image_url)
                                <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <span class="news-card-placeholder"><x-icon name="newspaper" /></span>
                            @endif
                            <span class="news-card-badge">{{ $item->category_label ?? __('School life') }}</span>
                        </div>
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
    </section>
</x-layouts.public>