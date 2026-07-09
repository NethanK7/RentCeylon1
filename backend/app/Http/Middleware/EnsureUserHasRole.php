<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role gate. Usage: ->middleware('role:admin') or 'role:lister,admin'.
 * Backs the RBAC requirement (renter/lister/admin/manager).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, 'You do not have access to this area.');
        }

        if ($user->isSuspended()) {
            abort(403, 'This account is suspended.');
        }

        return $next($request);
    }
}
