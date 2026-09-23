<x-layouts.public :title="__('Contact Us')"
    :description="__('Get in touch with our school — by phone, email, or a friendly visit.')"
>
    @php
        $appName = \App\Domains\Settings\Models\Setting::schoolName();
        $phone = \App\Domains\Settings\Models\Setting::value('school_phone', '+251 11 000 0000');
        $email = \App\Domains\Settings\Models\Setting::value('school_email', 'info@edusphere.com');
        $address = \App\Domains\Settings\Models\Setting::value('school_address', 'Addis Ababa, Ethiopia');
        $blocks = \App\Domains\Cms\Models\ContentBlock::forPage('contact');
        $siteBlocks = \App\Domains\Cms\Models\ContentBlock::forPage('site');
        $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);
        $hero = $blocks->get('hero');
        $hours = $siteBlocks->get('office-hours');
        $departments = $blocks->get('departments');
        $deptCards = (count($departments?->items ?? []) >= 1) ? $departments->items : [
            ['icon' => 'user', 'title' => __('Admissions Office'), 'text' => __('Applications, visits and enrolment questions — we would love to meet your family.')],
            ['icon' => 'clipboard-check', 'title' => __('Academic Office'), 'text' => __('Curriculum, reports, attendance and student wellbeing.')],
            ['icon' => 'wallet', 'title' => __('Finance & Billing'), 'text' => __('Fees, invoices, payment plans and scholarships.')],
            ['icon' => 'settings', 'title' => __('Parent Support & ICT'), 'text' => __('Portal access, transport and general family support.')],
        ];
    @endphp
    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ $tx($hero?->eyebrow) ?? __('We would love to hear from you') }}</p>
            <h1>{{ $tx($hero?->title) ?? __('Contact us') }}</h1>
            <p class="public-lead">{!! $tx($hero?->lead) ?? __('Questions, tours, or just a friendly hello — reach out any time.') !!}</p>
        </div>
    </section>

    <section class="public-section">
        <div class="contact-grid">
            <div class="contact-cards">
                <a class="contact-card" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">
                    <div class="contact-icon"><x-icon name="phone" /></div>
                    <b>{{ __('Call us') }}</b>
                    <span>{{ $phone }}</span>
                </a>
                <a class="contact-card" href="mailto:{{ $email }}">
                    <div class="contact-icon"><x-icon name="mail" /></div>
                    <b>{{ __('Email us') }}</b>
                    <span>{{ $email }}</span>
                </a>
                <div class="contact-card">
                    <div class="contact-icon"><x-icon name="map-pin" /></div>
                    <b>{{ __('Visit us') }}</b>
                    <span>{{ $address }}</span>
                </div>
                <div class="contact-card">
                    <div class="contact-icon"><x-icon name="clock" /></div>
                    <b>{{ $hours?->eyebrow ?? __('Office hours') }}</b>
                    <span>{!! $tx($hours?->body) ?? __('Mon–Fri · 8:00 am – 4:30 pm') !!}</span>
                </div>
            </div>

            <div class="contact-form-card">
                <h2>{{ __('Send us a message') }}</h2>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('public.inquiry.store') }}" class="public-form">
                    @csrf
                    <div class="form-grid">
                        @unless(auth()->check())
                            <div class="form-group">
                                <label for="full_name" class="form-label">{{ __('Full name') }}</label>
                                <input id="full_name" name="full_name" type="text" class="form-input" value="{{ old('full_name') }}" placeholder="{{ __('e.g. Sara Alemu') }}" required>
                                @error('full_name')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">{{ __('Email address') }}</label>
                                <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" placeholder="you@example.com" required>
                                @error('email')<span class="form-error">{{ $message }}</span>@enderror
                            </div>
                        @endunless
                        <div class="form-group">
                            <label for="type" class="form-label">{{ __('I\'m interested in') }}</label>
                            <select id="type" name="type" class="form-input">
                                <option value="admissions" @selected(old('type', 'admissions') === 'admissions')>{{ __('Admissions') }}</option>
                                <option value="visit" @selected(old('type') === 'visit')>{{ __('Book a school visit') }}</option>
                                <option value="general" @selected(old('type') === 'general')>{{ __('General question') }}</option>
                            </select>
                            @error('type')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="grade_level" class="form-label">{{ __('Grade level (optional)') }}</label>
                            <input id="grade_level" name="grade_level" type="text" class="form-input" value="{{ old('grade_level') }}" placeholder="{{ __('e.g. Grade 7, KG 2') }}">
                            @error('grade_level')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group form-group-full">
                            <label for="message" class="form-label">{{ __('Your message') }}</label>
                            <textarea id="message" name="message" rows="5" class="form-input">{{ old('message') }}</textarea>
                            @error('message')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Send message') }}</button>
                </form>
            </div>
        </div>
    </section>

    <section class="public-section public-section-alt">
        <div class="contact-depts">
            <div class="section-head">
                <p class="eyebrow">{{ $tx($departments?->eyebrow) ?? __('Who to contact') }}</p>
                <h2>{{ $tx($departments?->title) ?? __('Our departments') }}</h2>
                <p>{!! $tx($departments?->lead) ?? __('Don\'t know where to start? Our main office will point you in the right direction.') !!}</p>
            </div>
            <div class="req-grid">
                @foreach ($deptCards as $dept)
                    <div class="req-card">
                        <div class="contact-icon"><x-icon :name="$dept['icon'] ?? 'user'" /></div>
                        <h4>{{ $dept['title'] }}</h4>
                        <p>{{ $dept['text'] }}</p>
                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-map-embed">
            <iframe
                src="https://www.google.com/maps?q={{ urlencode($address) }}&output=embed"
                title="{{ \App\Domains\Settings\Models\Setting::schoolName() }} · {{ $address }}"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
            <div class="public-map-embed-caption">
                <x-icon name="map-pin" />
                <span>{{ \App\Domains\Settings\Models\Setting::schoolName() }} — {{ $address }}</span>
            </div>
        </div>
    </section>
</x-layouts.public>