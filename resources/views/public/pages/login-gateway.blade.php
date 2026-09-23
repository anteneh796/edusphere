<x-layouts.public :title="__('Portal Login')"
    :description="__('Access the Student, Parent or Staff portal — grades, attendance, billing and school updates in one secure place.')"
>
    @php
        $portals = [
            [
                'icon' => 'graduation-cap',
                'for' => __('For current students'),
                'title' => __('Student Portal'),
                'text' => __('View your timetable, attendance, results and school news from any device.'),
                'href' => route('cms.student.dashboard'),
            ],
            [
                'icon' => 'shield-heart',
                'for' => __('For parents & guardians'),
                'title' => __('Parent Portal'),
                'text' => __('Follow your child\'s progress, track bills and communicate with the school.'),
                'href' => route('cms.parent.dashboard'),
            ],
            [
                'icon' => 'users-support',
                'for' => __('For our staff'),
                'title' => __('Staff Portal'),
                'text' => __('Manage students, attendance, exams, content and administration securely.'),
                'href' => route('dashboard'),
            ],
        ];
    @endphp

    <section class="public-hero public-hero-compact">
        <div class="public-hero-inner">
            <p class="public-eyebrow">{{ __('Secure access') }}</p>
            <h1>{{ __('Portal Login') }}</h1>
            <p class="public-lead">{{ __('One account, one safe place. Sign in once and we will take you straight to the right portal for your role.') }}</p>
        </div>
    </section>

    <section class="public-section">
        <div class="gateway-grid">
            @foreach ($portals as $portal)
                <a href="{{ $portal['href'] }}" class="gateway-card">
                    <span class="gateway-icon"><x-icon name="{{ $portal['icon'] }}" /></span>
                    <span class="gateway-eyebrow">{{ $portal['for'] }}</span>
                    <h2>{{ $portal['title'] }}</h2>
                    <p>{{ $portal['text'] }}</p>
                    <span class="gateway-cta">
                        {{ __('Sign in') }} <x-icon name="arrow-right" />
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="public-section public-section-alt">
        <div class="section-head">
            <p class="eyebrow">{{ __('Need a hand?') }}</p>
            <h2>{{ __('Helpful tips') }}</h2>
        </div>
        <div class="gateway-tips">
            <div class="tip-card">
                <x-icon name="lock" />
                <h3>{{ __('Forgotten password?') }}</h3>
                <p>{!! __('Use the <a href=":link">password reset</a> link on the sign-in screen to get a secure reset email.', ['link' => route('auth.forgot')]) !!}</p>
            </div>
            <div class="tip-card">
                <x-icon name="users" />
                <h3>{{ __('New to the school?') }}</h3>
                <p>{!! __('Admissions can create accounts for new families. <a href=":link">Contact the front office</a> to get started.', ['link' => route('public.contact')]) !!}</p>
            </div>
            <div class="tip-card">
                <x-icon name="shield-check" />
                <h3>{{ __('Keeping you safe') }}</h3>
                <p>{{ __('Never share your password, and always sign out on shared devices. We will never ask for your password by message.') }}</p>
            </div>
        </div>
    </section>

    <section class="public-section">
        <div class="public-cta-band premium">
            <div>
                <h2>{{ __('Not part of our community yet?') }}</h2>
                <p>{{ __('We would love to welcome your family.') }}</p>
            </div>
            <div class="public-cta-actions">
                <a href="{{ route('public.apply') }}" class="btn btn-primary-light">{{ __('Apply Now') }}</a>
                <a href="{{ route('public.inquiry') }}" class="btn btn-ghost-light">{{ __('Send an inquiry') }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>