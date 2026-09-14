<?php

namespace App\Http\Middleware;

use App\Support\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Block suspended / inactive accounts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== UserStatus::Active->value) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Your account is '.$user->status.'. Contact the administrator.']);
        }

        return $next($request);
    }
}
