<?php

namespace App\Domains\Accounts\Services;

use App\Support\Enums\UserStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthService
{
    /**
     * Attempt to authenticate a user, rejecting inactive accounts.
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials, bool $remember = false): void
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            throw new AuthenticationException('Your account has been deactivated. Contact the administrator.');
        }

        Session::put('last_login_at', $user->updated_at);
    }

    public function logout(): void
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    public function statusLabel(string $status): string
    {
        return UserStatus::from($status)->label();
    }
}
