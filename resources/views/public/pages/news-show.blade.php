<x-layouts.public :title="$item->title">
    <article class="public-article">
        @if ($item->featured_image_url)
            <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" class="article-cover">
        @endif

        <header class="public-article-head">
            <div class="text-xs text-light">
                {{ optional($item->published_at)->format('M j, Y') ?? optional($item->created_at)->format('M j, Y') }}
            </div>
            <h1>{{ $item->title }}</h1>
            @if ($item->excerpt)
                <p class="article-lead">{{ $item->excerpt }}</p>
            @endif
        </header>

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
        <section class="public-section mt-6">
            <div class="public-section-head">
                <h2>Related Stories</h2>
            </div>
            <div class="public-grid news-grid">
                @foreach ($related as $item)
                    <a href="{{ route('cms.public.news-show', $item) }}" class="news-card">
                        <div class="news-card-body">
                            <h3 class="news-card-title">{{ $item->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
