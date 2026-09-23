<x-layouts.public :title="__('Academics')"
    :description="__('Our academic programmes from early years through Grade 8.')"
>
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('academics');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);

        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('Learning & academics') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('A path for every age') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('From joyful early years through Grade 8 — our programmes grow with your child.') !!}</p>
        </div>
    </section>

    @php
        $stagesBlock = $blocks->get('stages');
        $stages = ! empty($stagesBlock?->items) ? $stagesBlock->items : [
            ['icon' => 'sprout', 'title' => __('Early Years'), 'grades' => __('KG 1 – KG 2'), 'age' => __('Ages 4–6'), 'text' => __('Playful, language-rich discovery that builds confidence, curiosity and a lifelong love of learning.'), 'points' => [__('Learning through play and inquiry'), __('Phonics, numeracy and storytelling'), __('Music, movement and art every week')]],
            ['icon' => 'book-open', 'title' => __('Primary School'), 'grades' => __('Grades 1 – 6'), 'age' => __('Ages 6–12'), 'text' => __('Strong foundations in literacy, numeracy and character within a warm, structured classroom.'), 'points' => [__('English and Amharic literacy streams'), __('Singapore-style mathematics'), __('Science, ICT and PE each week')]],
            ['icon' => 'beaker', 'title' => __('Secondary School'), 'grades' => __('Grades 7 – 8'), 'age' => __('Ages 12–14'), 'text' => __('Inquiry-led learning across sciences, arts and humanities, with growing independence and responsibility.'), 'points' => [__('Project-based science and robotics'), __('Debate, drama and public speaking'), __('Preparations for national examinations')]],
        ];
    @endphp
    <section class="public-section">
        <div class="programme-stages">
            @foreach ($stages as $i => $stage)
                <article class="programme-stage" style="--i: {{ $i }}">
                    <div class="stage-icon"><x-icon :name="$stage['icon'] ?? 'book-open'" /></div>
                    <div class="stage-head">
                        <p class="stage-grades">{{ $stage['grades'] ?? '' }}@if (! empty($stage['age'])) · {{ $stage['age'] }}@endif</p>
                        <h2>{{ $stage['title'] }}</h2>
                    </div>
                    <p class="stage-desc">{{ $stage['text'] }}</p>
                    @if (! empty($stage['points']))
                        <ul class="stage-points">
                            @foreach ($stage['points'] as $point)
                                <li><x-icon name="check" /> {{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    @php
        $extrasBlock = $blocks->get('extras');
        $extras = ! empty($extrasBlock?->items) ? $extrasBlock->items : [
            ['icon' => 'beaker', 'title' => __('STEM & Innovation'), 'text' => __('Robotics, coding clubs and a makerspace where ideas become projects.')],
            ['icon' => 'music', 'title' => __('Arts & Performance'), 'text' => __('Choir, band, drama productions and the school-wide cultural festival.')],
            ['icon' => 'dumbbell', 'title' => __('Sports Programme'), 'text' => __('Football, basketball and athletics with coaching for all levels.')],
            ['icon' => 'globe', 'title' => __('Languages'), 'text' => __('English as the language of instruction with strong Amharic heritage.')],
        ];
    @endphp
    <section class="public-section public-section-alt">
        <x-public.section-head :block="$extrasBlock"
            eyebrow="{{ __('Beyond the classroom') }}"
            title="{{ __('Learning that extends everywhere') }}" />
        <div class="feature-grid">
            @foreach ($extras as $extra)
                <article class="feature-card">
                    <div class="feature-icon"><x-icon :name="$extra['icon'] ?? 'beaker'" /></div>
                    <div class="public-card-content">
                        <h3>{{ $extra['title'] }}</h3>
                        <p>{{ $extra['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @php
        $cta = $blocks->get('cta');
    @endphp
    <section class="public-section">
        <div class="public-simple-band">
            <h2>{{ $tx($cta?->title) ?? __('Ready to give your child a great start?') }}</h2>
            <p>{!! $tx($cta?->lead) ?? __('Contact our admissions team to learn more, or start your application today.') !!}</p>
            <div class="public-cta-actions">
                <a href="{{ route('public.apply') }}" class="btn btn-primary">{{ __('Apply Now') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-outline">{{ __('Ask a question') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>