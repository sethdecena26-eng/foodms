<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registered as 'has.role' in bootstrap/app.php.
 *
 * Runs on every authenticated route. If a user somehow has no role
 * assigned (e.g., created directly in the database without a role,
 * or a legacy account), they get a safe "pending approval" page
 * instead of a cryptic 500 error or a null-pointer crash.
 *
 * The dashboard route is always allowed through so the user can
 * at least see the holding message. All other routes are blocked
 * until an admin assigns them a role via /users.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not logged in — let the auth middleware handle it
        if (! $user) {
            return $next($request);
        }

        // User has a role assigned — all good, continue
        if (! is_null($user->role_id)) {
            return $next($request);
        }

        // No role assigned — only allow the dashboard (which shows the
        // "pending" message), block everything else
        if (! $request->routeIs('dashboard') && ! $request->routeIs('logout')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}