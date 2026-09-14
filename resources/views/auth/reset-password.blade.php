<x-layouts.auth :title="'Set new password'">
    <div class="auth-card-title">Set new password</div>
    <p class="auth-card-sub">Choose a strong password you haven't used before.</p>

    @if ($errors->any())
        <x-alert type="danger" class="mb-3">{{ $errors->first() }}</x-alert>
    @endif

    <form method="POST" action="{{ route('auth.reset.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <x-input
            name="password"
            type="password"
            label="New password"
            placeholder="Minimum 8 characters"
            required
            autofocus />

        <x-input
            name="password_confirmation"
            type="password"
            label="Confirm new password"
            placeholder="Repeat password"
            required />

        <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
            <x-icon name="check" class="icon-sm" />
            Update password
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('auth.login') }}" class="flex items-center justify-center gap-1">
            <x-icon name="arrow-left" class="icon-sm" />
            Back to sign in
        </a>
    </div>
</x-layouts.auth>