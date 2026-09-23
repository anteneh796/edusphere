<x-layouts.auth :title="__('Reset password')">
    <div class="auth-card-title">{{ __('Reset password') }}</div>
    <p class="auth-card-sub">{{ __('Enter your registered email and we will send you a reset link.') }}</p>

    @if (session('status'))
        <x-alert type="success" class="mb-3">{{ session('status') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="danger" class="mb-3">{{ $errors->first() }}</x-alert>
    @endif

    <form method="POST" action="{{ route('auth.forgot.send') }}" novalidate>
        @csrf

        <x-input
            name="email"
            type="email"
            :label="__('Email address')"
            placeholder="you@school.et"
            :value="old('email')"
            required
            autofocus />

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
            <x-icon name="mail" class="icon-sm" />
            {{ __('Send reset link') }}
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('auth.login') }}" class="flex items-center justify-center gap-1">
            <x-icon name="arrow-left" class="icon-sm" />
            {{ __('Back to sign in') }}
        </a>
    </div>
</x-layouts.auth>