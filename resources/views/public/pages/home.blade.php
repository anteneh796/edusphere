<x-layouts.public :title="__('Welcome')"
    :description="__('A nurturing learning community where every child grows, achieves and belongs.')"
>
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('home');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);
    @endphp

    {{-- 1. HERO --}}
    @php
        $hero = $blocks->get('hero');
        $heroTrust = ! empty($hero?->items) ? $hero->items : [
            ['icon' => 'check-circle', 'title' => __('Accredited curriculum')],
            ['icon' => 'check-circle', 'title' => __('Caring, qualified faculty')],
        ];
    @endphp
    <section class="public-hero" aria-label="{{ __('Welcome') }}">
        <div class="public-hero-inner">
            <div class="public-hero-copy">
                <p class="public-eyebrow">
                    <x-icon name="sparkles" />
                    {{ $tx($hero?->eyebrow) ?? __('Welcome to :school', ['school' => $appName]) }}
                </p>
                <h1>{{ $tx($hero?->title) ?? __('Where every child grows, achieves and belongs.') }}</h1>
                <p class="public-lead">
                    {!! $tx($hero?->lead) ?? __('A nurturing learning community committed to academic excellence, character, and service from early years through Grade 8.') !!}
                </p>
                <div class="public-hero-actions">
                    <a href="{{ route('public.apply') }}" class="btn btn-primary-light">
                        <x-icon name="clipboard-check" class="icon-sm" />
                        {{ __('Apply Now') }}
                    </a>
                    <a href="{{ route('public.academics') }}" class="btn btn-ghost-light">
                        {{ __('Explore Academics') }}
                        <x-icon name="arrow-right" class="icon-sm" />
                    </a>
                </div>
                @if ($heroTrust)
                    <div class="public-hero-trust">
                        @foreach ($heroTrust as $point)
                            <span><x-icon :name="$point['icon'] ?? 'check-circle'" /> {{ $point['title'] }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="public-hero-visual">
                <div class="hero-card hero-card-main">
                    <span class="hero-card-icon"><x-icon name="graduation" /></span>
                    <p>{{ __('A complete curriculum') }}</p>
                    <small>{{ __('Preschool through Grade 8') }}</small>
                </div>
                <div class="hero-card hero-card-accent">
                    <span class="hero-card-icon"><x-icon name="award" /></span>
                    <p>{{ __('15+ years of academic excellence') }}</p>
                </div>
                <div class="hero-card hero-card-soft">
                    <span class="hero-card-icon"><x-icon name="users" /></span>
                    <p>{{ __('A warm, diverse learning community') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. STATISTICS --}}
    @php
        $statsBlock = $blocks->get('stats');
        $defaultLabels = [__('Happy students'), __('Dedicated teachers'), __('Classes'), __('Subjects on offer')];
        $labels = count($statsBlock?->items ?? []) >= 2 ? $statsBlock->items : $defaultLabels;
        $values = [$studentCount, $teacherCount, $classRoomCount, $subjectCount];
    @endphp
    <section class="public-stats" aria-label="{{ __('School statistics') }}">
        <div class="public-stats-inner">
            @foreach ($labels as $index => $label)
                <div class="stat">
                    <b>{{ number_format($values[$index] ?? 0) }}</b>
                    <span>{{ $label['title'] ?? $label }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3. PRINCIPAL MESSAGE --}}
    @php
        $pm = $blocks->get('principal-message');
    @endphp
    <section class="public-section">
        <div class="principal-message">
            <div class="principal-portrait">
                <div class="principal-avatar">
                    <x-icon name="user" class="icon-lg" />
                </div>
                <p class="principal-name">{{ $principalName }}</p>
                <p class="principal-role">{{ __('Principal') }}</p>
            </div>
            <div class="principal-copy">
                <p class="public-eyebrow inline"><x-icon name="quote" /> {{ $tx($pm?->eyebrow) ?? __('A word from our Principal') }}</p>
                <h2>{{ $tx($pm?->title) ?? __('A community built on care and curiosity') }}</h2>
                <p class="principal-quote">
                    {!! $tx($pm?->body) ?? __('At :school, every child is known by name and loved as our own. We blend rigorous academics with the warmth of a real community, so your child leaves each day a little more confident, a little more curious, and a little more kind.', ['school' => $appName]) !!}
                </p>
                <a href="{{ route('public.about') }}" class="btn btn-outline btn-sm">
                    {{ __('Meet the :appName team', ['appName' => $appName]) }}
                    <x-icon name="arrow-right" class="icon-sm" />
                </a>
            </div>
        </div>
    </section>

    {{-- 4. ACADEMIC PROGRAMS --}}
    @php
        $prg = $blocks->get('programmes');
        $programs = ! empty($prg?->items) ? $prg->items : [
            ['icon' => 'sprout', 'title' => __('Early Years'), 'detail' => __('KG 1 – KG 2'), 'text' => __('Playful, language-rich discovery that builds a lifelong love of learning.')],
            ['icon' => 'book-open', 'title' => __('Primary'), 'detail' => __('Grades 1 – 6'), 'text' => __('Strong foundations in literacy, numeracy and character.')],
            ['icon' => 'beaker', 'title' => __('Secondary'), 'detail' => __('Grades 7 – 8'), 'text' => __('Inquiry-based learning across sciences, arts and humanities, with growing independence.')],
        ];
    @endphp
    <section class="public-section public-section-alt">
        <x-public.section-head :block="$prg"
            eyebrow="{{ __('Academic programmes') }}"
            title="{{ __('A path for every age') }}"
            lead="{{ __('From joyful early years to confident Grade 8 graduates, our programmes grow with your child.') }}" />
        <div class="program-grid">
            @foreach ($programs as $program)
                <article class="program-card">
                    <div class="program-icon"><x-icon :name="$program['icon'] ?? 'book-open'" /></div>
                    <p class="program-ages">{{ $program['detail'] ?? '' }}</p>
                    <h3>{{ $program['title'] }}</h3>
                    <p>{{ $program['text'] }}</p>
                    <a href="{{ route('public.academics') }}">
                        {{ __('Learn more') }} <x-icon name="arrow-right" />
                    </a>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 5. WHY CHOOSE US --}}
    @php
        $why = $blocks->get('why-us');
        $reasons = ! empty($why?->items) ? $why->items : [
            ['icon' => 'users', 'title' => __('Small class sizes'), 'text' => __('Every child is seen and supported — no one is left behind.')],
            ['icon' => 'award', 'title' => __('Qualified, caring faculty'), 'text' => __('Experienced teachers who know your child by name and by story.')],
            ['icon' => 'beaker', 'title' => __('Hands-on STEM labs'), 'text' => __('Inquiry, robotics and real experiments from the primary years.')],
            ['icon' => 'shield-heart', 'title' => __('Safe, joyful campus'), 'text' => __('A secure, green campus designed around children\'s wellbeing.')],
            ['icon' => 'globe', 'title' => __('English + Amharic'), 'text' => __('Bilingual excellence with strong national identity.')],
            ['icon' => 'music', 'title' => __('Arts, sport & clubs'), 'text' => __('Choir, football, debate and 20+ after-school clubs.')],
        ];
    @endphp
    <section class="public-section">
        <x-public.section-head :block="$why"
            eyebrow="{{ __('Why families choose us') }}"
            title="{{ __('The :school difference') }}"
            lead="{{ __('Six reasons our community is so special.') }}" />
        <div class="feature-grid wide">
            @foreach ($reasons as $reason)
                <article class="feature-card">
                    <div class="feature-icon"><x-icon :name="$reason['icon'] ?? 'check'" /></div>
                    <div class="public-card-content">
                        <h3>{{ $reason['title'] }}</h3>
                        <p>{{ $reason['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 6. FACILITIES --}}
    @php
        $fac = $blocks->get('facilities');
        $facilities = ! empty($fac?->items) ? $fac->items : [
            ['icon' => 'beaker', 'title' => __('STEM & Innovation Lab'), 'text' => __('3D printers, robotics and a makerspace for every grade.')],
            ['icon' => 'dumbbell', 'title' => __('Sports & Playgrounds'), 'text' => __('Pitch, courts and safe play areas for every age.')],
            ['icon' => 'music', 'title' => __('Music & Performing Arts Hall'), 'text' => __('Choir, instruments and theatrical productions.')],
            ['icon' => 'bus', 'title' => __('Transport & Safety'), 'text' => __('Guarded gates, CCTV and supervised school transport.')],
            ['icon' => 'sprout', 'title' => __('Green Campus'), 'text' => __('Gardens, trees and outdoor classrooms across campus.')],
        ];
    @endphp
    <section class="public-section public-section-alt">
        <x-public.section-head :block="$fac"
            eyebrow="{{ __('Campus life') }}"
            title="{{ __('Facilities designed for learning') }}"
            lead="{{ __('Modern spaces that spark curiosity and support wellbeing.') }}" />
        <div class="facility-grid">
            @foreach ($facilities as $facility)
                <article class="facility-card">
                    <div class="facility-icon"><x-icon :name="$facility['icon'] ?? 'school'" /></div>
                    <h3>{{ $facility['title'] }}</h3>
                    <p>{{ $facility['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 7. UPCOMING EVENTS --}}
    @php
        $ev = $blocks->get('events');
    @endphp
    <section class="public-section">
        <div class="public-section-head">
            <div>
                <p class="eyebrow left">{{ $tx($ev?->eyebrow) ?? __('What\'s happening') }}</p>
                <h2>{{ $tx($ev?->title) ?? __('Upcoming events') }}</h2>
            </div>
            <a href="{{ route('public.events') }}" class="btn btn-ghost btn-sm">{{ __('View calendar') }}</a>
        </div>

        @if ($events->isEmpty())
            <p class="public-empty">{{ __('No events are currently scheduled. Check back soon.') }}</p>
        @else
            <div class="events-strip">
                @foreach ($events as $event)
                    <article class="event-row">
                        <div class="event-date">
                            <strong>{{ $event->starts_at?->format('d') }}</strong>
                            <span>{{ $event->starts_at?->format('M') }}</span>
                        </div>
                        <div class="event-body">
                            <h3>{{ $event->title }}</h3>
                            @if ($event->location)
                                <p class="event-location"><x-icon name="map-pin" /> {{ $event->location }}</p>
                            @endif
                            <p class="event-time"><x-icon name="clock" /> {{ $event->starts_at?->format('g:i A') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 8. LATEST NEWS --}}
    @php
        $nw = $blocks->get('news');
    @endphp
    <section class="public-section public-section-alt">
        <div class="public-section-head">
            <div>
                <p class="eyebrow left">{{ $tx($nw?->eyebrow) ?? __('From the campus') }}</p>
                <h2>{{ $tx($nw?->title) ?? __('Latest news') }}</h2>
            </div>
            <a href="{{ route('public.news') }}" class="btn btn-ghost btn-sm">{{ __('All news') }}</a>
        </div>

        @if ($news->isEmpty())
            <p class="public-empty">{{ __('Check back soon for the latest updates.') }}</p>
        @else
            <div class="public-grid news-grid">
                @foreach ($news as $item)
                    <a href="{{ route('public.news-show', $item) }}" class="news-card">
                        <div class="news-card-media">
                            @if ($item->featured_image_url)
                                <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <span class="news-card-placeholder"><x-icon name="newspaper" /></span>
                            @endif
                            <span class="news-card-badge">{{ __('School life') }}</span>
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
        @endif
    </section>

    {{-- 9. STUDENT LIFE GALLERY --}}
    @php
        $gl = $blocks->get('gallery');
    @endphp
    <section class="public-section">
        <div class="public-section-head">
            <div>
                <p class="eyebrow left">{{ $tx($gl?->eyebrow) ?? __('Student life') }}</p>
                <h2>{{ $tx($gl?->title) ?? __('Campus in pictures') }}</h2>
            </div>
            <a href="{{ route('public.gallery') }}" class="btn btn-ghost btn-sm">{{ __('View gallery') }}</a>
        </div>

        @if ($gallery->isEmpty())
            <p class="public-empty">{{ __('Gallery photos will appear once they\'ve been published. Check back soon.') }}</p>
        @else
            <div class="gallery-strip">
                @foreach ($gallery as $item)
                    <a href="{{ route('public.gallery') }}" class="gallery-strip-tile">
                        @if ($item->isVideo())
                            <span class="gallery-strip-video"><x-icon name="video" /><strong>{{ __('Watch') }}</strong></span>
                        @elseif ($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->caption ?? __('Gallery photo') }}" loading="lazy">
                        @endif
                        @if ($item->caption)
                            <span class="gallery-strip-caption">{{ $item->caption }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 10. TESTIMONIALS --}}
    @php
        $ts = $blocks->get('testimonials');
    @endphp
    <section class="public-section">
        <x-public.section-head :block="$ts"
            eyebrow="{{ __('Kind words') }}"
            title="{{ __('What our families say') }}" />

        <div class="testimonial-grid">
            @forelse ($testimonials as $testimonial)
                <figure class="testimonial-card">
                    <x-icon name="quote" class="testimonial-quote" />
                    <blockquote>{{ $testimonial->quote }}</blockquote>
                    <figcaption>
                        @if ($testimonial->avatar_url)
                            <img src="{{ $testimonial->avatar_url }}" alt="{{ $testimonial->name }}" loading="lazy">
                        @else
                            <span class="testimonial-avatar">{{ str($testimonial->name)->initials() }}</span>
                        @endif
                        <span>
                            <strong>{{ $testimonial->name }}</strong>
                            <small>{{ $testimonial->role }}</small>
                        </span>
                    </figcaption>
                </figure>
            @empty
                <p class="public-empty">{{ __('Testimonials are being added. Check back soon.') }}</p>
            @endforelse
        </div>
    </section>

    {{-- 11. CTA --}}
    @php
        $cta = $blocks->get('cta');
    @endphp
    <section class="public-section">
        <div class="public-cta-band premium">
            <div>
                <p class="public-eyebrow inline">{{ $tx($cta?->eyebrow) ?? __('Enrolment open') }}</p>
                <h2>{{ $tx($cta?->title) ?? __('Take the first step with :school', ['school' => $appName]) }}</h2>
                <p>{!! $tx($cta?->lead) ?? __('Book a campus tour, ask a question, or submit your application today.') !!}</p>
            </div>
            <div class="public-cta-actions">
                <a href="{{ route('public.apply') }}" class="btn btn-primary-light">{{ __('Apply Now') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-ghost-light">{{ __('Book a visit') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>