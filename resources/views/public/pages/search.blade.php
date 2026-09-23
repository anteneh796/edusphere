<x-layouts.public :title="$query ? __('Search results for :query', ['query' => $query]) : __('Search')"
    :description="__('Search news, events and programmes across our website.')"
>
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow"><x-icon name="search" /> {{ __('Search') }}</p>
            <h1>{{ __('Search results') }}</h1>
            @if ($query)
                <p class="public-lead">{{ __('Showing results for ":query"', ['query' => $query]) }}</p>
            @endif
        </div>
    </section>

    <section class="public-section">
        <form method="GET" action="{{ route('public.search') }}" class="search-form-large" role="search">
            <div class="search-large-wrap">
                <x-icon name="search" />
                <input type="search" name="q" value="{{ $query }}" placeholder="{{ __('Search news, events, programmes…') }}" aria-label="Search keywords">
                <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
            </div>
        </form>

        <div class="search-results">
            @if ($query === '')
                <p class="search-hint">{{ __('Type something above and press Search to find content across our site.') }}</p>

            @elseif ($results->every(fn ($r) => $r->isEmpty()))
                <p class="search-empty">{{ __('No results found for ":query". Try a different search or', ['query' => $query]) }} <a href="{{ route('public.home') }}">{{ __('return home') }}</a>.</p>

            @else
                @php $totalResults = $results->sum(fn ($r) => $r->count()); @endphp
                <p class="search-count">{{ trans_choice(':count result found|:count results found', $totalResults, ['count' => $totalResults]) }}</p>

                @if ($results['pages']->isNotEmpty())
                    <div class="search-group">
                        <h2><x-icon name="file-text" /> {{ __('Pages') }}</h2>
                        @foreach ($results['pages'] as $page)
                            <a href="{{ route('public.page', $page->slug) }}" class="search-result-item">
                                <strong>{{ $page->title }}</strong>
                                @if ($page->excerpt)<span>{{ $page->excerpt }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($results['news']->isNotEmpty())
                    <div class="search-group">
                        <h2><x-icon name="newspaper" /> {{ __('News') }}</h2>
                        @foreach ($results['news'] as $item)
                            <a href="{{ route('public.news-show', $item) }}" class="search-result-item">
                                <strong>{{ $item->title }}</strong>
                                <span>{{ optional($item->published_at)->format('M j, Y') }} · {{ Str::limit(strip_tags((string) $item->content ?? $item->body), 120) }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($results['events']->isNotEmpty())
                    <div class="search-group">
                        <h2><x-icon name="calendar" /> {{ __('Events') }}</h2>
                        @foreach ($results['events'] as $event)
                            <div class="search-result-item">
                                <strong>{{ $event->title }}</strong>
                                <span>{{ $event->starts_at?->format('M j, Y g:i A') }} @if($event->location) · {{ $event->location }} @endif</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>
</x-layouts.public>