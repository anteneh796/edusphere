<x-layouts.auth :title="__('Sign in')">
    <div class="auth-card-title">{{ __('Sign in') }}</div>
    <p class="auth-card-sub">{{ __('Enter your credentials to access your account.') }}</p>

    @if ($errors->any())
        <x-alert type="danger" class="mb-3">{{ $errors->first() }}</x-alert>
    @endif

    <form method="POST" action="{{ route('auth.authenticate') }}" novalidate>
        @csrf

        <x-input
            name="login"
            type="text"
            :label="__('Email, username or ID')"
            placeholder="you@school.et"
            :value="old('login')"
            autocomplete="username"
            required
            autofocus />

        <x-input
            name="password"
            type="password"
            :label="__('Password')"
            placeholder="••••••••"
            autocomplete="current-password"
            required />

        @php
            $methods = collect([
                'email' => __('Email address'),
                'username' => __('Username'),
                'employee_id' => __('Employee ID'),
                'student_number' => __('Student number'),
            ])->filter(fn ($label, $key) => \App\Domains\Settings\Models\Setting::bool('login_method_'.$key, true));
        @endphp

        @if ($methods->isNotEmpty())
            <p class="auth-help">{{ __('You can sign in with any of:') }}
                {{ $methods->implode(', ') }}.</p>
        @endif

        <div class="flex items-center justify-between" style="margin: 4px 0 var(--space-2);">
            <x-checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />
            <a href="{{ route('auth.forgot') }}" class="text-sm font-semibold">{{ __('Forgot password?') }}</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
            <x-icon name="log-in" class="icon-sm" />
            {{ __('Sign in') }}
        </button>
    </form>

    <div class="auth-footer">
        {{ __('Having trouble? Contact the school administrator.') }}
    </div>
</x-layouts.auth>