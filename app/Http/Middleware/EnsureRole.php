<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Restrict access to a set of roles.
     *
     * Usage: ->middleware('role:principal,registrar')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('auth.login'));
        }

        $userHasRole = $user->roles()->whereIn('name', $roles)->exists();

        if (! $userHasRole) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
