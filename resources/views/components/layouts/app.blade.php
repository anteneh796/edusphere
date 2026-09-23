@props([
    'title' => null,
])

@php
    $appName = config('app.name', 'EduSphere');
    $user = auth()->user();
    $navSections = \App\Support\Navigation::forUser($user);
    $pageTitle = $title ? $title.' · '.$appName : $appName;
    $routeName = request()->route()?->getName();
    $currentLocale = app()->getLocale();
    $recentActivity = \App\Domains\Accounts\Models\AuditLog::with('user')->latest()->limit(6)->get();
    $canViewAudit = $user->hasRole([
        \App\Support\Enums\RoleName::SuperAdmin->value,
        \App\Support\Enums\RoleName::Principal->value,
    ]);
    $canViewNotifications = $user->hasPermission('notifications.view');
    $unreadNotifications = $canViewNotifications
        ? \App\Domains\Notifications\Models\Notification::where('user_id', $user->getKey())->unread()->count()
        : 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ $appName }}">
    <link rel="icon" href="/icons/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="app-shell">
    <div class="network-banner">
        <x-icon name="alert-triangle" class="icon-sm" />
        {{ __('You are offline. Changes will be saved and synced automatically.') }}
    </div>

    <div class="app-body">

        {{-- Sidebar --}}
        <aside class="sidebar" aria-label="Sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">E</div>
                <div>
                    <span class="sidebar-brand-name">EduSphere</span>
                    <span class="sidebar-brand-sub">{{ $appName }}</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                @foreach ($navSections as $section)
                    <div class="sidebar-section">
                        <div class="sidebar-section-title">{{ $section['title'] }}</div>
                        @foreach ($section['items'] as $item)
                            <a href="{{ route($item['route']) }}"
                                class="sidebar-link {{ $routeName === $item['route'] || str_starts_with($routeName ?? '', $item['route'].'.') ? 'active' : '' }}">
                                <x-icon :name="$item['icon']" class="icon-sm" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <button type="button" class="sidebar-collapse-btn" @click="$store.ui.collapseSidebar()">
                    <x-icon name="chevrons-left" class="icon-sm" />
                    <span>{{ __('Collapse') }}</span>
                </button>
            </div>
        </aside>

        <div class="sidebar-overlay" @click="$store.ui.closeMobileSidebar()"></div>

        {{-- Main column --}}
        <main class="app-main">
            <header class="app-topbar">
                <button type="button" class="topbar-menu-toggle" @click="$store.ui.mobileSidebarOpen = true" aria-label="Open menu">
                    <x-icon name="menu" />
                </button>

                <div class="topbar-search">
                    <x-icon name="search" class="icon-sm" />
                    <input type="search" placeholder="{{ __('Search students, invoices, subjects…') }}" aria-label="Search">
                </div>

                <div class="topbar-actions">
                    @php($userRoles = auth()->user()->roles)
                    @if ($userRoles->isNotEmpty())
                        <div class="role-chip-group" x-data="{ activeRole: null }" @click.outside="activeRole = null">
                            @foreach ($userRoles as $role)
                                <button
                                    type="button"
                                    class="role-chip @if ($loop->first) role-chip-active @endif"
                                    :class="{ 'role-chip-active': activeRole === {{ Js::from($role->name) }} }"
                                    @click="activeRole = activeRole === {{ Js::from($role->name) }} ? null : {{ Js::from($role->name) }}"
                                    title="Active role context"
                                >
                                    <x-icon name="shield-check" class="icon-sm" />
                                    {{ \App\Support\Enums\RoleName::from($role->name)->label() }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="dropdown" x-data="dropdown" @click.outside="open = false" :class="{ open: open }">
                        <button type="button" class="topbar-icon-btn" @click="toggle" :aria-label="__('Switch language')">
                            <x-icon name="languages" />
                        </button>

                        <div class="dropdown-menu" x-show="open" x-cloak x-transition style="right: 0;">
                            <div class="dropdown-header">
                                <b>{{ __('Language') }}</b>
                            </div>
                            <a href="{{ route('public.locale', 'en') }}" class="dropdown-item {{ $currentLocale === 'en' ? 'active' : '' }}">
                                English
                                @if ($currentLocale === 'en')<x-icon name="check" class="icon-sm" />@endif
                            </a>
                            <a href="{{ route('public.locale', 'am') }}" class="dropdown-item {{ $currentLocale === 'am' ? 'active' : '' }}">
                                አማርኛ
                                @if ($currentLocale === 'am')<x-icon name="check" class="icon-sm" />@endif
                            </a>
                        </div>
                    </div>

                    <div class="dropdown" x-data="dropdown" @click.outside="open = false" :class="{ open: open }">
                        <button type="button" class="topbar-icon-btn" @click="toggle" aria-label="Notifications">
                            <x-icon name="bell" />
                            @if ($unreadNotifications > 0)
                                <span class="badge-count">{{ min($unreadNotifications, 99) }}</span>
                            @elseif ($recentActivity->isNotEmpty())
                                <span class="dot"></span>
                            @endif
                        </button>

                        <div class="dropdown-menu" x-show="open" x-cloak x-transition style="width: 320px; right: 0;">
                            <div class="dropdown-header">
                                <b>{{ __('Recent activity') }}</b>
                                @if ($unreadNotifications > 0)
                                    <span class="text-xs text-muted">{{ __(':count unread notifications', ['count' => $unreadNotifications]) }}</span>
                                @endif
                            </div>
                            <div style="max-height: 320px; overflow-y: auto;">
                                @forelse ($recentActivity as $log)
                                    <div class="dropdown-item" style="align-items:flex-start; gap: var(--space-2); cursor:default;">
                                        <x-avatar :initials="optional($log->user)->initials() ?? '–'" size="sm" />
                                        <div style="min-width:0; flex:1;">
                                            <div class="truncate" style="font-size: var(--text-sm); font-weight: var(--weight-semibold);">{{ $log->action }}</div>
                                            <div class="text-xs text-muted">{{ str($log->module)->title() }} · {{ $log->user?->full_name ?? 'System' }}</div>
                                        </div>
                                        <time class="text-xs text-muted">{{ $log->created_at->diffForHumans() }}</time>
                                    </div>
                                @empty
                                    <div class="dropdown-item">
                                        <div class="text-muted text-sm">{{ __('No activity recorded yet.') }}</div>
                                    </div>
                                @endforelse
                            </div>
                            @if ($canViewAudit)
                                <a href="{{ route('audit.index') }}" class="dropdown-item" style="font-weight: var(--weight-semibold);">
                                    <x-icon name="shield-check" class="icon-sm" />
                                    {{ __('View full audit log') }}
                                </a>
                            @endif
                            @if ($canViewNotifications)
                                <a href="{{ route('notifications.index') }}" class="dropdown-item" style="font-weight: var(--weight-semibold);">
                                    <x-icon name="bell" class="icon-sm" />
                                    {{ __('View all notifications') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="dropdown" x-data="dropdown" @click.outside="open = false" :class="{ open: open }">
                        <button type="button" class="topbar-icon-btn" @click="toggle" aria-label="Account menu">
                            <x-avatar :initials="$user->initials()" size="sm" />
                        </button>

                        <div class="dropdown-menu" x-show="open" x-cloak x-transition>
                            <div class="dropdown-header">
                                <b>{{ $user->full_name }}</b>
                                <span>{{ $user->email }}</span>
                            </div>
                            <a href="{{ route('profile.index') }}" class="dropdown-item">
                                <x-icon name="user" class="icon-sm" />
                                {{ __('My Profile') }}
                            </a>
                            <a href="{{ route('profile.security') }}" class="dropdown-item">
                                <x-icon name="lock" class="icon-sm" />
                                {{ __('Change Password') }}
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <x-icon name="log-out" class="icon-sm" />
                                    {{ __('Sign out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="app-content">
                @if (session('status'))
                    <x-alert type="success" class="mb-3">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any() && request()->routeIs('auth.*'))
                    <x-alert type="danger" class="mb-3">
                        {{ $errors->first() }}
                    </x-alert>
                @endif

                {{ $slot }}
            </div>

            <footer style="padding: 0 var(--space-4) var(--space-4); text-align:center; font-size:var(--text-xs); color:var(--color-text-light);">
                © {{ date('Y') }} {{ $appName }} · {{ __('School Management System') }}
            </footer>
        </main>
    </div>

    <x-confirm-modal />
    @stack('scripts')
</body>
</html>