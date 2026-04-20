<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:
 *   Route::middleware(['auth', 'role:admin'])
 *   Route::middleware(['auth', 'role:admin,employee'])
 *
 * Registered as alias 'role' in bootstrap/app.php.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Null-safe: if role is null, name will be null, won't match any role
        if (! in_array($user->role?->name, $roles, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}