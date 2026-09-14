<x-layouts.public :title="$tag">
    <article class="public-article">
        <header class="public-article-head">
            <h1>{{ $page?->title ?? $tag }}</h1>
            @if ($page?->excerpt)
                <p class="article-lead">{{ $page->excerpt }}</p>
            @endif
        </header>

        <div class="prose">
            {!! $page?->content ?? '<p>This page is coming soon.</p>' !!}
        </div>

        @if ($page?->published_at)
            <p class="article-meta">Last updated {{ $page->published_at->diffForHumans() }}</p>
        @endif
    </article>
</x-layouts.public>
