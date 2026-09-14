@props([
    'title' => null,
])

@php
    $appName = config('app.name', 'EduSphere');
    $pageTitle = $title ? $title.' · '.$appName : $appName;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/icons/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-layout">
        <div class="auth-panel">
            <div class="auth-brand">
                <div class="auth-logo">E</div>
                <h1 style="font-size: var(--text-2xl);">Welcome back to <br><span class="text-primary">EduSphere</span></h1>
                <p class="text-muted mt-2" style="font-size: var(--text-sm);">
                    One platform to manage your entire school — students, classes, attendance,
                    examinations, fees and reports.
                </p>

                <div class="auth-stat-grid">
                    <div class="auth-stat">
                        <b>1K+</b>
                        <span>Schools</span>
                    </div>
                    <div class="auth-stat">
                        <b>50K+</b>
                        <span>Students</span>
                    </div>
                    <div class="auth-stat">
                        <b>24/7</b>
                        <span>Support</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-wrap">
            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>