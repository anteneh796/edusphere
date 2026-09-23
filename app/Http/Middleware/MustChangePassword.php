<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    /**
     * Redirect authenticated users who must reset their password to the
     * security panel until they comply.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->requiresPasswordChange() && ! $request->routeIs('profile.security')) {
            return redirect()->route('profile.security')
                ->with('status', __('For security, please set a new password before continuing.'));
        }

        return $next($request);
    }
}
