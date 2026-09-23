<x-layouts.public :title="__('Photo & Video Gallery')"
    :description="__('Moments from around the school — classrooms, campus life, ceremonies, celebrations and films.')"
>
    @php
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('gallery');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', \App\Domains\Settings\Models\Setting::schoolName(), $v);
        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('Campus life') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('Photo & Video Gallery') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('A window into our days — celebrations, classrooms, sports, films and the quiet moments in between.') !!}</p>
        </div>
    </section>

    <section class="public-section">
        @if ($items->isEmpty())
            <p class="public-empty">{{ __('Gallery photos will appear once they\'ve been published. Check back soon.') }}</p>
        @else
            @foreach ($items->groupBy(fn ($item) => $item->album ?: __('General')) as $album => $albumItems)
                <div class="gallery-album">
                    <h2 class="gallery-album-title">{{ $album }}</h2>
                    <div class="gallery-grid">
                        @foreach ($albumItems as $item)
                            @if ($item->isVideo())
                                <figure class="gallery-tile gallery-tile-video">
                                    <div class="video-tile">
                                        @if ($item->video_embed_url)
                                            <iframe src="{{ $item->video_embed_url }}" title="{{ $item->caption ?? __('School video') }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                        @else
                                            <span class="video-tile-placeholder"><x-icon name="video" /></span>
                                        @endif
                                    </div>
                                    @if ($item->caption)
                                        <figcaption>{{ $item->caption }}</figcaption>
                                    @endif
                                </figure>
                            @else
                                <figure class="gallery-tile">
                                    @if ($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->caption ?? __('Gallery photo') }}" loading="lazy">
                                    @endif
                                    @if ($item->caption)
                                        <figcaption>{{ $item->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if (method_exists($items, 'links'))
                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            @endif
        @endif
    </section>
</x-layouts.public>