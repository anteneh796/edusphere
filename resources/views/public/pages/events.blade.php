<x-layouts.public :title="__('Events')"
    description="{{ __('Upcoming school events, tournaments, ceremonies, and community gatherings — save the date.') }}"
>
    @php
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('events');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', \App\Domains\Settings\Models\Setting::schoolName(), $v);
        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('School Calendar') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('Events') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('Mark your calendar — our community comes together throughout the year.') !!}</p>
        </div>
    </section>

    <section class="public-section">
        @forelse ($events as $month => $monthEvents)
            <div class="public-events-month">
                <h2 class="public-events-month-title">{{ $month }}</h2>
                <div class="public-events-list">
                    @foreach ($monthEvents as $event)
                        <article class="event-row {{ $event->featured ? 'event-row-featured' : '' }}">
                            <div class="event-date">
                                <strong>{{ $event->starts_at?->format('d') }}</strong>
                                <span>{{ $event->starts_at?->format('M') }}</span>
                            </div>
                            <div class="event-body">
                                <h3>
                                    {{ $event->title }}
                                    @if ($event->featured)
                                        <span class="event-featured-badge"><x-icon name="star" /> {{ __('Featured') }}</span>
                                    @endif
                                </h3>
                                @if ($event->location)
                                    <p class="event-location">{{ $event->location }}</p>
                                @endif
                                @if ($event->starts_at)
                                    <p class="event-time">
                                        {{ $event->starts_at->format('g:i A') }}@if ($event->ends_at) – {{ $event->ends_at->format('g:i A') }}@endif
                                    </p>
                                @endif
                                @if ($event->description)
                                    <p class="event-description">{{ $event->description }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="public-empty">{{ __('No events are currently scheduled. Check back soon.') }}</p>
        @endforelse
    </section>
</x-layouts.public>
