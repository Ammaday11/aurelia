<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every /admin route beyond plain authentication. Today every
 * registered user is an admin (see User::isAdmin()), but routing all
 * admin-only checks through this single middleware means a future staff
 * role split only needs to change one place.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
