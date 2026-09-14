@props([
    'title' => null,
])

@php
    $appName = config('app.name', 'EduSphere');
    $user = auth()->user();
    $navSections = \App\Support\Navigation::forUser($user);
    $pageTitle = $title ? $title.' · '.$appName : $appName;
    $routeName = request()->route()?->getName();
    $recentActivity = \App\Domains\Accounts\Models\AuditLog::with('user')->latest()->limit(6)->get();
    $canViewAudit = $user->hasRole([
        \App\Support\Enums\RoleName::SuperAdmin->value,
        \App\Support\Enums\RoleName::Principal->value,
    ]);
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
        You are offline. Changes will be saved and synced automatically.
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
                    <span>Collapse</span>
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
                    <input type="search" placeholder="Search students, invoices, subjects…" aria-label="Search">
                </div>

                <div class="topbar-actions">
                    <div class="dropdown" x-data="dropdown" @click.outside="open = false">
                        <button type="button" class="topbar-icon-btn" @click="toggle" aria-label="Notifications">
                            <x-icon name="bell" />
                            @if ($recentActivity->isNotEmpty())
                                <span class="dot"></span>
                            @endif
                        </button>

                        <div class="dropdown-menu" x-show="open" x-cloak x-transition style="width: 320px; right: 0;">
                            <div class="dropdown-header">
                                <b>Recent activity</b>
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
                                        <div class="text-muted text-sm">No activity recorded yet.</div>
                                    </div>
                                @endforelse
                            </div>
                            @if ($canViewAudit)
                                <a href="{{ route('audit.index') }}" class="dropdown-item" style="font-weight: var(--weight-semibold);">
                                    <x-icon name="shield-check" class="icon-sm" />
                                    View full audit log
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="dropdown" x-data="dropdown" @click.outside="open = false">
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
                                My Profile
                            </a>
                            <a href="{{ route('profile.security') }}" class="dropdown-item">
                                <x-icon name="lock" class="icon-sm" />
                                Change Password
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <x-icon name="log-out" class="icon-sm" />
                                    Sign out
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
                © {{ date('Y') }} {{ $appName }} · School Management System
            </footer>
        </main>
    </div>

    <x-confirm-modal />
    @stack('scripts')
</body>
</html>