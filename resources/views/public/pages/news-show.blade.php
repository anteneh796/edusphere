<x-layouts.public :title="$item->title"
    :description="$item->excerpt"
    :image="$item->featured_image_url"
    type="article"
>
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ __('From the campus') }}</p>
            <h1>{{ $item->title }}</h1>
            <p class="public-article-meta">
                @if ($item->category_label)
                    <span class="news-card-badge">{{ $item->category_label }}</span>
                @endif
                {{ optional($item->published_at)->format('M j, Y') ?? optional($item->created_at)->format('M j, Y') }}
                @if ($item->author)
                    · {{ __('By') }} {{ $item->author->full_name }}
                @endif
                @if ($item->tags->isNotEmpty())
                    · {{ $item->tags->pluck('name')->map(fn ($tag) => '#'.$tag)->join(' ') }}
                @endif
            </p>
        </div>
    </section>

    <article class="public-article">
        @if ($item->featured_image_url)
            <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" class="article-cover">
        @endif

        @if ($item->excerpt)
            <p class="article-lead">{{ $item->excerpt }}</p>
        @endif

        <div class="prose">
            {!! $item->content !!}
        </div>

        @if ($item->tags->isNotEmpty())
            <div class="article-tags mt-3">
                @foreach ($item->tags as $tag)
                    <span class="badge badge-neutral">#{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section class="public-section public-section-alt">
            <div class="public-section-head">
                <div>
                    <p class="eyebrow left">{{ __('Keep reading') }}</p>
                    <h2>{{ __('Related Stories') }}</h2>
                </div>
                <a href="{{ route('public.news') }}" class="btn btn-ghost btn-sm">{{ __('All news') }}</a>
            </div>
            <div class="public-grid news-grid">
                @foreach ($related as $relatedItem)
                    <a href="{{ route('public.news-show', $relatedItem) }}" class="news-card">
                        <div class="news-card-media">
                            @if ($relatedItem->featured_image_url)
                                <img src="{{ $relatedItem->featured_image_url }}" alt="{{ $relatedItem->title }}" loading="lazy">
                            @else
                                <span class="news-card-placeholder"><x-icon name="newspaper" /></span>
                            @endif
                            <span class="news-card-badge">{{ $relatedItem->category_label ?? __('School life') }}</span>
                        </div>
                        <div class="news-card-body">
                            <div class="text-xs text-light">
                                {{ optional($relatedItem->published_at)->format('M j, Y') ?? optional($relatedItem->created_at)->format('M j, Y') }}
                            </div>
                            <h3 class="news-card-title">{{ $relatedItem->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="public-section">
        <div class="public-cta-band premium">
            <div>
                <h2>{{ __('Want stories like this first?') }}</h2>
                <p>{{ __('Sign in to your portal or reach out — we would love to hear from you.') }}</p>
            </div>
            <div class="public-cta-actions">
                <a href="{{ route('public.login-gateway') }}" class="btn btn-primary-light">{{ __('Portal Login') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-ghost-light">{{ __('Get in touch') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>