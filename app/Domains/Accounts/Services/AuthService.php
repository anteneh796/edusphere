<?php

namespace App\Domains\Accounts\Services;

use App\Domains\Accounts\Models\LoginLog;
use App\Domains\Accounts\Models\User;
use App\Domains\Settings\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Enums\UserStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Authenticate a user by any enabled identifier (email, username,
     * employee id or student number), record the session and audit trail.
     *
     * @throws AuthenticationException
     */
    public function login(string $identifier, string $password, bool $remember = false): User
    {
        $user = $this->resolveUser(trim($identifier));

        if (! $user) {
            $this->recordFailedLogin($identifier);

            throw new AuthenticationException('These credentials do not match our records.');
        }

        if (! Hash::check($password, $user->password)) {
            $this->recordFailedLogin($identifier, $user);

            throw new AuthenticationException('These credentials do not match our records.');
        }

        if (! $user->isActive()) {
            $this->recordFailedLogin($identifier, $user);

            throw new AuthenticationException('Your account has been deactivated. Contact the administrator.');
        }

        Auth::login($user, $remember);

        // Rotate the session id before persisting any login markers so the
        // recorded session id is the one the browser keeps.
        Session::regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->saveQuietly();

        $this->establishSession($user, $identifier);

        return $user;
    }

    public function logout(): void
    {
        $user = auth()->user();
        $logId = session()->get('login_log_id');

        if ($logId) {
            LoginLog::whereKey($logId)->update(['logout_at' => now(), 'last_activity_at' => now()]);
        }

        if ($user && $user->status === UserStatus::Active->value) {
            ActivityLogger::log('logged out', 'auth', recordId: $logId ? (string) $logId : null, userId: (string) $user->getKey());
        }

        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    public function statusLabel(string $status): string
    {
        return UserStatus::from($status)->label();
    }

    /**
     * Find a user by one of the enabled login identifiers.
     */
    protected function resolveUser(string $identifier): ?User
    {
        $methods = $this->enabledIdentifierColumns();

        if (empty($methods)) {
            $methods = ['email'];
        }

        return User::query()
            ->where(function ($query) use ($identifier, $methods) {
                $lower = strtolower($identifier);

                foreach ($methods as $column) {
                    if ($column === 'email' || $column === 'username') {
                        $query->orWhereRaw('LOWER('.$column.') = ?', [$lower]);
                    } else {
                        $query->orWhere($column, $identifier);
                    }
                }
            })
            ->first();
    }

    /**
     * Columns that may be used to sign in, driven by Settings > Security.
     */
    protected function enabledIdentifierColumns(): array
    {
        $methods = [];

        foreach ([
            'email' => 'login_method_email',
            'username' => 'login_method_username',
            'employee_id' => 'login_method_employee_id',
            'student_number' => 'login_method_student_id',
        ] as $column => $setting) {
            if (Setting::bool($setting, true)) {
                $methods[] = $column;
            }
        }

        return $methods;
    }

    protected function establishSession(User $user, string $identifier): void
    {
        $role = $user->roles()->value('name');

        $timeout = Setting::value('session_timeout_'.$role) !== null
            ? (int) Setting::value('session_timeout_'.$role)
            : (int) config('rbac.session_timeouts.'.$role, Setting::value('session_timeout_global', 60));

        Session::put('session_timeout_minutes', max(0, $timeout));
        Session::put('last_activity_at', now()->timestamp);

        $userAgent = (string) request()->userAgent();

        $log = LoginLog::create([
            'user_id' => $user->getKey(),
            'event' => 'login',
            'identifier' => Str::limit($identifier, 150),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit($userAgent, 500),
            'device' => $this->detectDevice($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'login_at' => now(),
            'last_activity_at' => now(),
            'session_id' => session()->getId(),
        ]);

        Session::put('login_log_id', $log->getKey());
        Session::put('_login_log_sync_at', 0);

        ActivityLogger::log('user signed in', 'auth', recordId: (string) $log->getKey(), userId: (string) $user->getKey());
    }

    protected function recordFailedLogin(string $identifier, ?User $user = null): void
    {
        LoginLog::create([
            'user_id' => $user?->getKey(),
            'event' => 'failed',
            'identifier' => Str::limit($identifier, 150),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 500),
            'login_at' => now(),
        ]);

        ActivityLogger::log('failed login attempt', 'auth', userId: $user ? (string) $user->getKey() : null);
    }

    protected function detectDevice(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            Str::contains($ua, ['iphone', 'android', 'mobile']) => 'mobile',
            Str::contains($ua, ['ipad', 'tablet']) => 'tablet',
            default => 'desktop',
        };
    }

    protected function detectPlatform(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            Str::contains($ua, 'android') => 'Android',
            Str::contains($ua, ['iphone', 'ipad', 'ipod']) => 'iOS',
            Str::contains($ua, ['mac os', 'macintosh']) => 'macOS',
            Str::contains($ua, 'windows') => 'Windows',
            Str::contains($ua, 'linux') => 'Linux',
            default => 'Other',
        };
    }

    protected function detectBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            Str::contains($ua, 'edg/') => 'Edge',
            Str::contains($ua, ['opr/', 'opera']) => 'Opera',
            Str::contains($ua, 'vivaldi') => 'Vivaldi',
            Str::contains($ua, 'brave') => 'Brave',
            Str::contains($ua, 'chrome') => 'Chrome',
            Str::contains($ua, 'firefox') => 'Firefox',
            Str::contains($ua, 'safari') => 'Safari',
            default => 'Other',
        };
    }
}
