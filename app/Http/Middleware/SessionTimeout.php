<?php

namespace App\Http\Middleware;

use App\Domains\Accounts\Models\LoginLog;
use App\Domains\Settings\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Enforce the per-role idle session timeout and track last activity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $timeoutMinutes = (int) $request->session()->get(
                'session_timeout_minutes',
                Setting::value('session_timeout_global', 60)
            );

            $now = now()->timestamp;
            $lastActivity = (int) $request->session()->get('last_activity_at', $now);

            if ($timeoutMinutes > 0 && $now - $lastActivity > $timeoutMinutes * 60) {
                return $this->expire($request, $user);
            }

            $request->session()->put('last_activity_at', $now);
            $this->touchLoginLog($request, $now);
        }

        return $next($request);
    }

    private function expire(Request $request, $user): Response
    {
        $logId = $request->session()->get('login_log_id');
        $userId = $user->getKey();

        if ($logId && Schema::hasTable('login_logs')) {
            LoginLog::whereKey($logId)->update(['last_activity_at' => now(), 'logout_at' => now()]);
            LoginLog::create([
                'user_id' => $userId,
                'event' => 'expired',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'login_at' => now(),
            ]);
        }

        if ($user->status === UserStatus::Active->value) {
            ActivityLogger::log('session expired due to inactivity', 'auth', $userId);
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login')
            ->withErrors(['login' => __('Your session expired due to inactivity. Please sign in again.')]);
    }

    private function touchLoginLog(Request $request, int $now): void
    {
        if (! Schema::hasTable('login_logs')) {
            return;
        }

        $logId = $request->session()->get('login_log_id');

        if (! $logId) {
            return;
        }

        $nextSync = (int) $request->session()->get('_login_log_sync_at', 0);

        if ($nextSync > $now) {
            return;
        }

        $request->session()->put('_login_log_sync_at', $now + 60);

        LoginLog::whereKey($logId)->update(['last_activity_at' => now()]);
    }
}
