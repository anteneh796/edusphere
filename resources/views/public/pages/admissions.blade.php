<x-layouts.public :title="__('Admissions')"
    :description="__('Learn how to join our community — steps, fees and our open-enrolment policy.')"
>
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('admissions');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);

        $hero = $blocks->get('hero');
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('Join our community') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('Admissions') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('Every child is welcome here. Our open enrolment policy means spaces are available across all grades throughout the year.') !!}</p>
        </div>
    </section>

    @php
        $stepsBlock = $blocks->get('steps');
        $steps = ! empty($stepsBlock?->items) ? $stepsBlock->items : [
            ['title' => __('Enquire'), 'text' => __('Send us an inquiry or book a campus tour. Our admissions team will respond within one working day.')],
            ['title' => __('Apply'), 'text' => __('Submit the application form with a copy of your child\'s records. A small application fee applies.')],
            ['title' => __('Meet us'), 'text' => __('A friendly assessment and conversation help us understand your child\'s needs and strengths.')],
            ['title' => __('Welcome aboard'), 'text' => __('Once accepted, you\'ll receive your offer letter, welcome pack and all the details you need.')],
        ];
    @endphp
    <section class="public-section">
        <div class="admit-steps">
            @foreach ($steps as $index => $step)
                <article class="admit-step">
                    <span class="step-num">{{ $step['n'] ?? ($index + 1) }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    @php
        $docsBlock = $blocks->get('documents');
        $docCards = ! empty($docsBlock?->items) ? $docsBlock->items : [
            ['title' => __('Required documents'), 'points' => [__('Completed application form'), __('Birth certificate or passport copy'), __('Most recent report card / transcripts'), __('Two recent passport photos'), __('Medical / immunisation record')]],
            ['title' => __('Good to know'), 'points' => [__('Open enrolment all year, subject to space'), __('Sibling discounts available'), __('Scholarships for outstanding students'), __('Termly fees with flexible payment plans')]],
        ];
    @endphp
    <section class="public-section public-section-alt">
        <x-public.section-head :block="$docsBlock"
            eyebrow="{{ __('What to prepare') }}"
            title="{{ __('Documents & requirements') }}" />
        <div class="req-grid">
            @foreach ($docCards as $card)
                <div class="req-card">
                    <h4>{{ $card['title'] }}</h4>
                    @if (! empty($card['points']))
                        <ul>
                            @foreach ($card['points'] as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    @php
        $ageBlock = $blocks->get('age-table');
        $ageRows = ! empty($ageBlock?->items) ? $ageBlock->items : [
            ['title' => 'KG 1', 'text' => __('4–5 years')],
            ['title' => 'KG 2', 'text' => __('5–6 years')],
            ['title' => 'Grade 1', 'text' => __('6–7 years')],
            ['title' => 'Grade 2', 'text' => __('7–8 years')],
            ['title' => 'Grade 3', 'text' => __('8–9 years')],
            ['title' => 'Grade 4', 'text' => __('9–10 years')],
            ['title' => 'Grade 5', 'text' => __('10–11 years')],
            ['title' => 'Grade 6', 'text' => __('11–12 years')],
            ['title' => 'Grade 7', 'text' => __('12–13 years')],
            ['title' => 'Grade 8', 'text' => __('13–14 years')],
        ];
    @endphp
    <section class="public-section">
        <x-public.section-head :block="$ageBlock"
            eyebrow="{{ __('Placement') }}"
            title="{{ __('Age criteria by grade') }}"
            lead="{{ __('Each level follows the Ethiopian academic year. Children should have turned the required age by September 30 of the admission year.') }}" />
        @if ($ageRows)
            <div class="age-table-wrap">
                <table class="age-table">
                    <thead>
                        <tr>
                            <th>{{ __('Grade level') }}</th>
                            <th>{{ __('Typical age by Sep 30') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ageRows as $row)
                            <tr>
                                <td>{{ $row['title'] }}</td>
                                <td>{{ $row['text'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @php
        $faqBlock = $blocks->get('faq');
        $faqs = ! empty($faqBlock?->items) ? $faqBlock->items : [];
    @endphp
    <section class="public-section public-section-alt">
        <x-public.section-head :block="$faqBlock"
            eyebrow="{{ __('Good to know') }}"
            title="{{ __('Frequently asked questions') }}" />
        @if ($faqs)
            <div class="faq-list">
                @foreach ($faqs as $index => $faq)
                    <details class="faq-item" @if ($index === 0) open @endif>
                        <summary>
                            <span>{{ $faq['title'] }}</span>
                            <x-icon name="chevron-down" />
                        </summary>
                        <p>{{ $faq['text'] }}</p>
                    </details>
                @endforeach
            </div>
        @endif
    </section>

    @php
        $cta = $blocks->get('cta');
    @endphp
    <section class="public-section">
        <div class="public-cta-band premium">
            <div>
                <h2>{{ $tx($cta?->title) ?? __('Start your application today') }}</h2>
                <p>{!! $tx($cta?->lead) ?? __('We would love to welcome your family.') !!}</p>
            </div>
            <div class="public-cta-actions">
                <a href="{{ route('public.apply') }}" class="btn btn-primary-light">{{ __('Apply Now') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-ghost-light">{{ __('Book a campus tour') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>