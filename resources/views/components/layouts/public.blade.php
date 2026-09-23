@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $appName = \App\Domains\Settings\Models\Setting::schoolName();
    $tagline = \App\Domains\Settings\Models\Setting::value('school_tagline', 'Knowledge is Light');
    $email = \App\Domains\Settings\Models\Setting::value('school_email', 'info@edusphere.com');
    $phone = \App\Domains\Settings\Models\Setting::value('school_phone', '+251 11 000 0000');
    $address = \App\Domains\Settings\Models\Setting::value('school_address', 'Addis Ababa, Ethiopia');
    $pageTitle = $title ? $title.' · '.$appName : $appName;
    $pageDescription = $description ?? ($slot ?? '');
    $routeName = request()->route()?->getName();
    $currentLocale = app()->getLocale();
    $localeName = $currentLocale === 'am' ? 'አማርኛ' : 'English';
    $activePage = collect(['about', 'academics', 'admissions', 'contact', 'apply'])->first(
        fn ($p) => $routeName === 'public.'.$p
    );
    $activeMega = collect(['academics', 'admissions'])->contains($activePage);
    $user = auth()->user();
    $announcement = \App\Domains\Settings\Models\Setting::value('announcement_text');
    $announcementEnabled = (bool) \App\Domains\Settings\Models\Setting::value('announcement_enabled', false);
    $officeHours = \App\Domains\Cms\Models\ContentBlock::forPage('site')->get('office-hours');
    $school = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);
    $social = [
        'facebook' => \App\Domains\Settings\Models\Setting::value('social_facebook'),
        'instagram' => \App\Domains\Settings\Models\Setting::value('social_instagram'),
        'telegram' => \App\Domains\Settings\Models\Setting::value('social_telegram'),
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ $appName }}">
    <meta name="description" content="{{ strip_tags((string) $pageDescription) ?: __('A modern school community committed to academic excellence, character, and service.') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0C2E2B">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="/icons/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <title>{{ $pageTitle }}</title>

    {{-- Open Graph / social --}}
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ strip_tags((string) $pageDescription) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($image)<meta property="og:image" content="{{ $image }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ strip_tags((string) $pageDescription) }}">

    {{-- Structured data --}}
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => $appName,
        'slogan' => $tagline,
        'url' => url('/'),
        'telephone' => $phone,
        'email' => $email,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressCountry' => 'ET',
        ],
        'sameAs' => [],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="public-body" x-data="publicSite()" :data-theme="theme">
    <a class="public-skip" href="#public-main">Skip to content</a>

    {{-- Cookie notice --}}
    <div class="cookie-notice" x-show="showCookie" x-cloak x-transition x-transition.opacity.duration.300ms>
        <div class="cookie-notice-inner">
            <p>{{ __('We use cookies to improve your experience on our website. By continuing, you agree to our use of cookies.') }}</p>
            <div class="cookie-actions">
                <button type="button" class="btn btn-primary btn-sm" @click="acceptCookies()">{{ __('Accept all') }}</button>
                <button type="button" class="btn btn-ghost btn-sm" @click="rejectCookies()">{{ __('Essential only') }}</button>
            </div>
        </div>
    </div>

    {{-- Announcement bar --}}
    @if ($announcementEnabled && $announcement)
        <div class="public-announcement" role="region" aria-label="{{ __('Announcement') }}">
            <div class="public-announcement-inner">
                <x-icon name="bell" />
                <p>{{ $announcement }}</p>
            </div>
        </div>
    @endif

    <header class="public-header" @keydown.escape.window="open = false; drawer = false; searchOpen = false">
        {{-- Utility bar --}}
        <div class="public-utility">
            <div class="public-utility-inner">
                <div class="public-utility-contacts">
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"><x-icon name="phone" /> {{ $phone }}</a>
                    <a href="mailto:{{ $email }}"><x-icon name="mail" /> {{ $email }}</a>
                </div>
                <div class="public-utility-tools">
                    <a href="{{ route('public.faculty') }}">{{ __('Our Team') }}</a>
                    <a href="{{ route('public.apply') }}">{{ __('Enrol your child') }}</a>
                    <div class="public-utility-sep"></div>
                    {{-- Language switch --}}
                    <div class="lang-switch" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                        <button type="button" class="lang-toggle" @click="langOpen = !langOpen" aria-label="{{ __('Switch language') }}">
                            <x-icon name="languages" />
                            <span>{{ $localeName }}</span>
                            <x-icon name="chevron-down" />
                        </button>
                        <div class="lang-menu" x-show="langOpen" x-cloak x-transition>
                            <a href="{{ route('public.locale', 'en') }}" class="{{ $currentLocale === 'en' ? 'active' : '' }}">
                                English
                            </a>
                            <a href="{{ route('public.locale', 'am') }}" class="{{ $currentLocale === 'am' ? 'active' : '' }}">
                                አማርኛ
                            </a>
                        </div>
                    </div>
                    {{-- Theme switch --}}
                    <button type="button" class="theme-toggle" @click="toggleTheme()" :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'">
                        <x-icon name="sun" x-show="theme === 'dark'" />
                        <x-icon name="moon" x-show="theme === 'light'" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Main bar --}}
        <div class="public-header-main">
            <div class="public-header-inner" @click.outside="open = false">
                <a href="{{ route('public.home') }}" class="public-brand">
                    <span class="public-brand-mark">E</span>
                    <span>
                        <span class="public-brand-name">{{ $appName }}</span>
                        <span class="public-brand-sub">{{ $tagline }}</span>
                    </span>
                </a>

                <nav class="public-nav {{ $routeName === 'public.home' ? 'is-home' : '' }}" aria-label="{{ __('Main') }}" :class="{ open: open }">
                    <a href="{{ route('public.home') }}" class="{{ $routeName === 'public.home' ? 'active' : '' }}">{{ __('Home') }}</a>
                    <a href="{{ route('public.about') }}" class="{{ $activePage === 'about' ? 'active' : '' }}">{{ __('About') }}</a>

                    {{-- Mega menu trigger --}}
                    <div class="nav-item has-mega" x-data="{ mega: false }"
                        @mouseenter="mega = true" @mouseleave="mega = false"
                        @keydown.escape.window="mega = false">
                        <button type="button" class="nav-trigger {{ $activeMega ? 'active' : '' }}" @click="mega = !mega" aria-expanded="false" :aria-expanded="mega.toString()" @focus="mega = true">
                            {{ __('Learning') }} <x-icon name="chevron-down" />
                        </button>
                        <div class="mega-menu" x-show="mega" x-cloak x-transition x-transition.origin.top>
                            <div class="mega-inner">
                                <div class="mega-col">
                                    <p class="mega-title">{{ __('Programmes') }}</p>
                                    <a href="{{ route('public.academics') }}">{{ __('Early Years (KG 1–2)') }}</a>
                                    <a href="{{ route('public.academics') }}">{{ __('Primary (Grades 1–6)') }}</a>
                                    <a href="{{ route('public.academics') }}">{{ __('Secondary (Grades 7–8)') }}</a>
                                </div>
                                <div class="mega-col">
                                    <p class="mega-title">{{ __('Explore') }}</p>
                                    <a href="{{ route('public.news') }}">{{ __('News & Stories') }}</a>
                                    <a href="{{ route('public.events') }}">{{ __('School Events') }}</a>
                                    <a href="{{ route('public.gallery') }}">{{ __('Photo Gallery') }}</a>
                                    <a href="{{ route('public.faculty') }}">{{ __('Meet the Faculty') }}</a>
                                </div>
                                <div class="mega-feature">
                                    <p class="public-eyebrow">{{ __('Admissions open') }}</p>
                                    <h4>{{ __('Join :school', ['school' => $appName]) }}</h4>
                                    <p>{{ __('Applications are open for the coming academic year. We would love to meet your family.') }}</p>
                                    <a href="{{ route('public.apply') }}" class="btn btn-primary btn-sm">{{ __('Apply Now') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('public.events') }}" class="{{ $routeName === 'public.events' ? 'active' : '' }}">{{ __('Events') }}</a>
                    <a href="{{ route('public.admissions') }}" class="{{ $activePage === 'admissions' ? 'active' : '' }}">{{ __('Admissions') }}</a>
                    <a href="{{ route('public.news') }}" class="{{ str_starts_with($routeName ?? '', 'public.news') ? 'active' : '' }}">{{ __('News') }}</a>
                    <a href="{{ route('public.gallery') }}" class="{{ $routeName === 'public.gallery' ? 'active' : '' }}">{{ __('Gallery') }}</a>
                    <a href="{{ route('public.contact') }}" class="{{ $activePage === 'contact' ? 'active' : '' }}">{{ __('Contact') }}</a>
                </nav>

                <div class="public-cta">
                    <button type="button" class="public-icon-btn" @click="searchOpen = true" aria-label="{{ __('Search the website') }}">
                        <x-icon name="search" />
                    </button>
                    <a href="{{ route('public.apply') }}" class="btn btn-primary btn-sm apply-cta">
                        <x-icon name="clipboard-check" class="icon-sm" />
                        {{ __('Apply Now') }}
                    </a>
                    <div class="public-cta-login">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                                <x-icon name="user" class="icon-sm" />
                                {{ __('My Portal') }}
                            </a>
                        @else
                            <a href="{{ route('auth.login') }}" class="btn btn-outline btn-sm">
                                <x-icon name="log-in" class="icon-sm" />
                                {{ __('Log in') }}
                            </a>
                        @endauth
                    </div>
                    <button type="button" class="public-menu-toggle" @click="drawer = !drawer" aria-label="{{ __('Toggle menu') }}" aria-expanded="false" :aria-expanded="drawer.toString()">
                        <x-icon name="menu" />
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Search overlay --}}
    <div class="search-overlay" x-show="searchOpen" x-cloak x-transition.opacity>
        <div class="search-overlay-panel" @click.outside="searchOpen = false">
            <div class="search-overlay-head">
                <h3>{{ __('Search') }} {{ $appName }}</h3>
                <button type="button" class="public-icon-btn" @click="searchOpen = false" aria-label="{{ __('Close search') }}">
                    <x-icon name="x" />
                </button>
            </div>
            <form method="GET" action="{{ route('public.search') }}" class="search-form" role="search">
                <x-icon name="search" />
                <input type="search" name="q" placeholder="{{ __('Search news, events, programmes…') }}" autofocus aria-label="{{ __('Search keywords') }}">
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Search') }}</button>
            </form>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div class="public-drawer" :class="{ open: drawer }" @click.outside="drawer = false">
        <div class="public-drawer-head">
            <span class="public-drawer-brand">{{ $appName }}</span>
            <button type="button" class="public-drawer-close" @click="drawer = false" aria-label="{{ __('Close menu') }}">
                <x-icon name="x" />
            </button>
        </div>
        <nav class="public-drawer-nav" aria-label="{{ __('Mobile') }}">
            <a href="{{ route('public.home') }}" class="{{ $routeName === 'public.home' ? 'active' : '' }}">{{ __('Home') }}</a>
            <a href="{{ route('public.about') }}" class="{{ $activePage === 'about' ? 'active' : '' }}">{{ __('About the School') }}</a>
            <a href="{{ route('public.academics') }}" class="{{ $activePage === 'academics' ? 'active' : '' }}">{{ __('Learning & Academics') }}</a>
            <a href="{{ route('public.admissions') }}" class="{{ $activePage === 'admissions' ? 'active' : '' }}">{{ __('Admissions') }}</a>
            <a href="{{ route('public.news') }}" class="{{ str_starts_with($routeName ?? '', 'public.news') ? 'active' : '' }}">{{ __('News & Announcements') }}</a>
            <a href="{{ route('public.events') }}" class="{{ $routeName === 'public.events' ? 'active' : '' }}">{{ __('Events') }}</a>
            <a href="{{ route('public.gallery') }}" class="{{ $routeName === 'public.gallery' ? 'active' : '' }}">{{ __('Gallery') }}</a>
            <a href="{{ route('public.faculty') }}" class="{{ $routeName === 'public.faculty' ? 'active' : '' }}">{{ __('Faculty & Staff') }}</a>
            <a href="{{ route('public.contact') }}" class="{{ $activePage === 'contact' ? 'active' : '' }}">{{ __('Contact') }}</a>
            <a href="{{ route('public.apply') }}" class="{{ $activePage === 'apply' ? 'active' : '' }}">{{ __('Apply Now') }}</a>
        </nav>
        <div class="public-drawer-tools">
            <form method="GET" action="{{ route('public.search') }}" class="search-form search-form-small" role="search">
                <x-icon name="search" />
                <input type="search" name="q" placeholder="{{ __('Search…') }}" aria-label="{{ __('Search keywords') }}">
            </form>
            <div class="public-drawer-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">{{ __('Open my portal') }}</a>
                @else
                    <a href="{{ route('auth.login') }}" class="btn btn-outline btn-block">
                        <x-icon name="log-in" class="icon-sm" />
                        {{ __('Staff / Student Login') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>

    @php
        $crumbs = [];

        if ($routeName !== 'public.home') {
            $crumbs[] = ['label' => __('Home'), 'url' => route('public.home')];

            $trail = [
                'public.about' => __('About'),
                'public.academics' => __('Academics'),
                'public.admissions' => __('Admissions'),
                'public.contact' => __('Contact us'),
                'public.apply' => __('Apply'),
                'public.events' => __('Events'),
                'public.gallery' => __('Gallery'),
                'public.news' => __('News & Stories'),
                'public.inquiry' => __('Inquiry'),
                'public.faculty' => __('Our Team'),
                'public.search' => __('Search'),
                'public.login-gateway' => __('Portal Login'),
            ];

            if ($routeName === 'public.news-show') {
                $crumbs[] = ['label' => __('News & Stories'), 'url' => route('public.news')];
                $crumbs[] = ['label' => __('Article')];
            } elseif (isset($trail[$routeName])) {
                $crumbs[] = ['label' => $trail[$routeName]];
            } elseif ($routeName === 'public.page') {
                $crumbs[] = ['label' => __(ucwords(str_replace('-', ' ', request()->route()->parameter('page'))))];
            } else {
                $crumbs[] = ['label' => __('Page')];
            }
        }
    @endphp

    <main id="public-main">
        @if (! empty($crumbs))
            <nav class="public-breadcrumbs" aria-label="{{ __('Breadcrumbs') }}">
                <ol class="public-breadcrumbs-list">
                    @foreach ($crumbs as $index => $crumb)
                        <li>
                            @if (isset($crumb['url']) && $index !== array_key_last($crumbs))
                                <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                            @else
                                <span aria-current="page">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="public-footer">
        <div class="public-footer-top">
            <div class="public-footer-inner">
                <div class="about">
                    <h4>{{ $appName }}</h4>
                    <p>{{ $tagline }}. {{ __('A modern school community committed to academic excellence, character, and service.') }}</p>
                    <div class="public-footer-social">
                        @foreach ($social as $name => $url)
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($name) }}">
                                    <x-icon :name="$name === 'telegram' ? 'send' : ($name === 'instagram' ? 'camera' : 'external')" />
                                </a>
                            @endif
                        @endforeach
                        @unless(array_filter($social))
                            <a href="#" aria-label="Facebook"><x-icon name="external" /></a>
                        @endunless
                    </div>
                    <form method="POST" action="{{ route('public.newsletter.subscribe') }}" class="footer-newsletter">
                        @csrf
                        <label for="newsletter_email">{{ __('Get school updates by email') }}</label>
                        <div class="footer-newsletter-row">
                            <input id="newsletter_email" type="email" name="email" placeholder="you@example.com" required>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Subscribe') }}</button>
                        </div>
                        @error('email')
                            <span class="footer-newsletter-error">{{ $message }}</span>
                        @else
                            @if (session('newsletter'))
                                <span class="footer-newsletter-success">{{ session('newsletter') }}</span>
                            @endif
                        @enderror
                    </form>
                </div>
                <div>
                    <h4>{{ __('Explore') }}</h4>
                    <ul>
                        <li><a href="{{ route('public.about') }}">{{ __('About the School') }}</a></li>
                        <li><a href="{{ route('public.academics') }}">{{ __('Academics') }}</a></li>
                        <li><a href="{{ route('public.admissions') }}">{{ __('Admissions') }}</a></li>
                        <li><a href="{{ route('public.faculty') }}">{{ __('Faculty & Staff') }}</a></li>
                        <li><a href="{{ route('public.page', 'privacy') }}">{{ __('Privacy Policy') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('Community') }}</h4>
                    <ul>
                        <li><a href="{{ route('public.news') }}">{{ __('News') }}</a></li>
                        <li><a href="{{ route('public.events') }}">{{ __('Events Calendar') }}</a></li>
                        <li><a href="{{ route('public.gallery') }}">{{ __('Photo Gallery') }}</a></li>
                        <li><a href="{{ route('public.apply') }}">{{ __('Apply Now') }}</a></li>
                        <li><a href="{{ route('public.login-gateway') }}">{{ __('Portal Login') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('Visit us') }}</h4>
                    <ul class="footer-contact">
                        <li><x-icon name="map-pin" /> {{ $address }}</li>
                        <li><a href="mailto:{{ $email }}"><x-icon name="mail" /> {{ $email }}</a></li>
                        <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"><x-icon name="phone" /> {{ $phone }}</a></li>
                    </ul>
                    <div class="footer-hours">
                        <strong>{{ $officeHours?->eyebrow ?? __('Office hours') }}</strong>
                        <span>{!! $school($officeHours?->body) ?? __('Mon–Fri · 8:00 am – 4:30 pm') !!}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="public-footer-bottom">
            <div class="public-footer-bottom-inner">
                <span>© {{ date('Y') }} {{ $appName }} · {{ __('School Management System') }}</span>
                <span>{{ __('Designed with care for our community') }}</span>
            </div>
        </div>
    </footer>

    <script>
        function publicSite() {
            return {
                open: false,
                drawer: false,
                searchOpen: false,
                theme: localStorage.getItem('public-theme') || 'light',
                showCookie: !localStorage.getItem('public-cookie'),
                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('public-theme', this.theme);
                },
                acceptCookies() {
                    localStorage.setItem('public-cookie', 'accepted');
                    this.showCookie = false;
                },
                rejectCookies() {
                    localStorage.setItem('public-cookie', 'essential');
                    this.showCookie = false;
                },
            };
        }
    </script>
</body>
</html>