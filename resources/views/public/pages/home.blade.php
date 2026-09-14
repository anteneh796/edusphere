<x-layouts.public :title="'Home'">
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $tagline = \App\Domains\Settings\Models\Setting::value('school_tagline', 'Knowledge is Light');
    @endphp

    <div class="public-hero">
        <div class="public-hero-inner">
            <span class="hero-eyebrow">Welcome to {{ $appName }}</span>
            <h1>{{ $page?->title ?? $appName }}</h1>
            <p class="hero-lead">
                {{ $page?->excerpt ?? 'A nurturing learning community where every child grows, achieves and belongs.' }}
            </p>
            <div class="hero-actions">
                <a href="{{ route('cms.public.page', 'admissions') }}" class="btn btn-primary">Apply Now</a>
                <a href="{{ route('cms.public.news') }}" class="btn btn-ghost-light">Latest News</a>
            </div>
        </div>

        <div class="hero-stats">
            <div class="stat"><b>{{ number_format($studentCount) }}</b><span>Students</span></div>
            <div class="stat"><b>{{ number_format($classRoomCount) }}</b><span>Classes</span></div>
            <div class="stat"><b>{{ number_format($teacherCount) }}</b><span>Teachers</span></div>
            <div class="stat"><b>{{ number_format($subjectCount) }}</b><span>Subjects</span></div>
        </div>
    </div>

    @if ($page?->content)
        <section class="public-section">
            <div class="prose">
                {!! $page->content !!}
            </div>
        </section>
    @endif

    @if ($news->isNotEmpty())
        <section class="public-section">
            <div class="public-section-head">
                <h2>Latest News</h2>
                <a href="{{ route('cms.public.news') }}" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="public-grid news-grid">
                @foreach ($news as $item)
                    <a href="{{ route('cms.public.news-show', $item) }}" class="news-card">
                        <div class="news-card-body">
                            <div class="text-xs text-light">{{ $item->published_at?->format('M j, Y') }}</div>
                            <h3 class="news-card-title">{{ $item->title }}</h3>
                            @if ($item->excerpt)
                                <p class="news-card-excerpt">{{ $item->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($gallery->isNotEmpty())
        <section class="public-section">
            <div class="public-section-head">
                <h2>From Our Gallery</h2>
                <a href="{{ route('cms.public.gallery') }}" class="btn btn-ghost btn-sm">View gallery</a>
            </div>
            <div class="public-grid gallery-grid">
                @foreach ($gallery as $item)
                    <figure class="gallery-tile">
                        @if ($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->caption ?? $item->title }}" loading="lazy">
                        @endif
                        @if ($item->caption)
                            <figcaption>{{ $item->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
