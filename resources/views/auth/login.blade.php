<x-layouts.auth :title="'Sign in'">
    <div class="auth-card-title">Sign in</div>
    <p class="auth-card-sub">Enter your credentials to access your account.</p>

    @if ($errors->any())
        <x-alert type="danger" class="mb-3">{{ $errors->first() }}</x-alert>
    @endif

    <form method="POST" action="{{ route('auth.authenticate') }}" novalidate>
        @csrf

        <x-input
            name="email"
            type="email"
            label="Email address"
            placeholder="you@school.et"
            :value="old('email')"
            required
            autofocus />

        <x-input
            name="password"
            type="password"
            label="Password"
            placeholder="••••••••"
            required />

        <div class="flex items-center justify-between" style="margin: 4px 0 var(--space-2);">
            <x-checkbox name="remember" label="Remember me" :checked="old('remember')" />
            <a href="{{ route('auth.forgot') }}" class="text-sm font-semibold">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
            <x-icon name="log-in" class="icon-sm" />
            Sign in
        </button>
    </form>

    <div class="auth-footer">
        Having trouble? Contact the school administrator.
    </div>
</x-layouts.auth>