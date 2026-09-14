@props([
    'title' => null,
])

@php
    $appName = \App\Domains\Settings\Models\Setting::schoolName();
    $tagline = \App\Domains\Settings\Models\Setting::value('school_tagline', 'Knowledge is Light');
    $email = \App\Domains\Settings\Models\Setting::value('school_email', 'info@edusphere.com');
    $phone = \App\Domains\Settings\Models\Setting::value('school_phone', '+251 11 000 0000');
    $address = \App\Domains\Settings\Models\Setting::value('school_address', 'Addis Ababa, Ethiopia');
    $pageTitle = $title ? $title.' · '.$appName : $appName;
    $routeName = request()->route()?->getName();
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ $appName }}">
    <link rel="icon" href="/icons/icon-192.png">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="public-body">
    <header class="public-header">
        <div class="public-header-inner" x-data="{ navOpen: false }" @click.outside="navOpen = false">
            <a href="{{ route('public.home') }}" class="public-brand">
                <span class="public-brand-mark">E</span>
                <span>
                    <span class="public-brand-name">{{ $appName }}</span>
                    <span class="public-brand-sub">{{ $tagline }}</span>
                </span>
            </a>

            <nav class="public-nav" :class="{ open: navOpen }" aria-label="Main">
                <a href="{{ route('public.home') }}" class="{{ $routeName === 'public.home' ? 'active' : '' }}">Home</a>
                <a href="{{ route('cms.public.page', 'about') }}" class="{{ ($routeName === 'cms.public.page' && request()->route()->parameter('page') === 'about') ? 'active' : '' }}">About</a>
                <a href="{{ route('cms.public.page', 'academics') }}" class="{{ ($routeName === 'cms.public.page' && request()->route()->parameter('page') === 'academics') ? 'active' : '' }}">Academics</a>
                <a href="{{ route('cms.public.page', 'admissions') }}" class="{{ ($routeName === 'cms.public.page' && request()->route()->parameter('page') === 'admissions') ? 'active' : '' }}">Admissions</a>
                <a href="{{ route('cms.public.news') }}" class="{{ str_starts_with($routeName ?? '', 'cms.public.news') ? 'active' : '' }}">News</a>
                <a href="{{ route('cms.public.gallery') }}" class="{{ $routeName === 'cms.public.gallery' ? 'active' : '' }}">Gallery</a>
                <a href="{{ route('cms.public.page', 'contact') }}" class="{{ ($routeName === 'cms.public.page' && request()->route()->parameter('page') === 'contact') ? 'active' : '' }}">Contact</a>
            </nav>

            <div class="public-cta">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Open my portal</a>
                @else
                    <a href="{{ route('auth.login') }}" class="btn btn-outline btn-sm">
                        <x-icon name="log-in" class="icon-sm" />
                        Staff Login
                    </a>
                @endauth
                <button type="button" class="public-menu-toggle" @click="navOpen = !navOpen" aria-label="Toggle menu">
                    <x-icon name="menu" />
                </button>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="public-footer">
        <div class="public-footer-inner">
            <div class="about">
                <h4>{{ $appName }}</h4>
                <p>{{ $tagline }}. A modern school community committed to academic excellence, character, and service since our founding.</p>
            </div>
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="{{ route('cms.public.page', 'about') }}">About Us</a></li>
                    <li><a href="{{ route('cms.public.page', 'academics') }}">Academics</a></li>
                    <li><a href="{{ route('cms.public.page', 'admissions') }}">Admissions</a></li>
                    <li><a href="{{ route('cms.public.news') }}">News &amp; Events</a></li>
                </ul>
            </div>
            <div>
                <h4>Community</h4>
                <ul>
                    <li><a href="{{ route('cms.public.gallery') }}">Photo Gallery</a></li>
                    <li><a href="{{ route('cms.public.page', 'contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('auth.login') }}">Staff / Student Portal</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li>{{ $address }}</li>
                    <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                    <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
                </ul>
            </div>
        </div>
        <div class="public-footer-bottom">
            © {{ date('Y') }} {{ $appName }} · School Management System
        </div>
    </footer>
</body>
</html>