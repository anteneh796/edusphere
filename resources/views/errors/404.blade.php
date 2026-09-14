<!-- 404 -->
<x-layouts.auth :title="'Page not found'">
    <div style="text-align:center; padding: var(--space-5) 0;">
        <div class="empty-state-icon" style="margin: 0 auto var(--space-3); background: var(--color-warning-soft); color: var(--color-warning);">
            <x-icon name="compass" class="icon-lg" />
        </div>
        <h1 style="font-size: 56px; line-height:1; letter-spacing:-0.03em;">404</h1>
        <p class="text-muted mt-2">The page you are looking for does not exist or has been moved.</p>
        <div class="mt-4">
            <a href="{{ auth()->check() ? route('dashboard') : route('auth.login') }}" class="btn btn-primary">
                <x-icon name="arrow-left" class="icon-sm" />
                Back to {{ auth()->check() ? 'dashboard' : 'login' }}
            </a>
        </div>
    </div>
</x-layouts.auth>