<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Restrict access to any of the given permissions.
     *
     * Usage: ->middleware('permission:students.view')
     *        ->middleware('permission:students.view,students.edit')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('auth.login'));
        }

        if (! $user->hasAnyPermission($permissions)) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
