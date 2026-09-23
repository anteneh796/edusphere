<x-layouts.public :title="__('About') . ' ' . \App\Domains\Settings\Models\Setting::schoolName()"
    :description="__('A modern learning community committed to academic excellence, character, and service.')"
>
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('about');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);

        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('Our story') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('About :school', ['school' => $appName]) }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('A modern learning community committed to academic excellence, character, and service.') !!}</p>
        </div>
    </section>

    {{-- Mission / vision / values --}}
    @php
        $mv = $blocks->get('mv-cards');
        $mvCards = ! empty($mv?->items) ? $mv->items : [
            ['icon' => 'target', 'title' => __('Our Mission'), 'text' => __('To inspire every student to become a curious, confident, and compassionate citizen ready to lead in Ethiopia and beyond.')],
            ['icon' => 'eye', 'title' => __('Our Vision'), 'text' => __('A joyful, inclusive community where every child is known, challenged, and supported to discover their full potential.')],
            ['icon' => 'heart', 'title' => __('Our Values'), 'text' => __('Integrity, curiosity, respect, resilience, and service guide everything we do — in the classroom and beyond it.')],
        ];
    @endphp
    <section class="public-section">
        <div class="mv-grid">
            @foreach ($mvCards as $card)
                <article class="mv-card">
                    <div class="mv-icon"><x-icon :name="$card['icon'] ?? 'target'" /></div>
                    <h2>{{ $card['title'] }}</h2>
                    <p>{{ $card['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Our story --}}
    @php
        $story = $blocks->get('story');
        $pillars = count($story?->items ?? []) === count($stats) ? $story->items : null;
    @endphp
    <section class="public-section public-section-alt">
        <div class="split-layout">
            <div class="split-copy">
                <p class="eyebrow left">{{ $tx($story?->eyebrow) ?? __('A 15-year journey') }}</p>
                <h2>{{ $tx($story?->title) ?? __('From one classroom to a vibrant campus') }}</h2>
                <div class="prose">
                    {!! $tx($story?->body) ?? '<p>' . __('Founded with a belief that every child deserves an education that is both challenging and caring, our school has grown from a single classroom into a full preschool through Grade 8 campus.') . '</p>' !!}
                </div>
                <div class="about-pillars">
                    @foreach ($stats as $index => $stat)
                        <div><b>{{ number_format($stat['value']) }}</b><span>{{ $pillars[$index]['title'] ?? $stat['label'] }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="split-visual">
                <div class="split-card split-card-main"><x-icon name="school" /><span>{{ __('Preschool → Grade 8') }}</span></div>
                <div class="split-card split-card-soft"><x-icon name="users" /><span>{{ __('Diverse community') }}</span></div>
                <div class="split-card split-card-accent"><x-icon name="award" /><span>{{ __('Accredited academics') }}</span></div>
            </div>
        </div>
    </section>

    {{-- Leadership --}}
    @php
        $leaders = $blocks->get('leadership');
        $leadership = ! empty($leaders?->items) ? $leaders->items : [
            ['title' => '', 'text' => __('Principal')],
            ['title' => 'Hirut Lemma', 'text' => __('Registrar')],
            ['title' => 'Selamawit Haile', 'text' => __('Director of Finance')],
            ['title' => 'Mulugeta Assefa', 'text' => __('Head of Academics')],
        ];
    @endphp
    <section class="public-section">
        <x-public.section-head :block="$leaders"
            eyebrow="{{ __('Leadership') }}"
            title="{{ __('Meet our leadership team') }}" />
        <div class="leadership-grid">
            @foreach ($leadership as $index => $leader)
                <article class="leader-card">
                    <div class="leader-avatar"><x-icon name="user" class="icon-lg" /></div>
                    <h3>{{ $index === 0 && empty($leader['title']) ? $principalName : $leader['title'] }}</h3>
                    <p>{{ $leader['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    @php
        $cta = $blocks->get('cta');
    @endphp
    <section class="public-section">
        <div class="public-cta-band premium">
            <div>
                <h2>{{ $tx($cta?->title) ?? __('Come see our campus for yourself') }}</h2>
                <p>{!! $tx($cta?->lead) ?? __('We would love to welcome your family on a guided tour.') !!}</p>
            </div>
            <div class="public-cta-actions">
                <a href="{{ route('public.apply') }}" class="btn btn-primary-light">{{ __('Apply Now') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-ghost-light">{{ __('Book a visit') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>