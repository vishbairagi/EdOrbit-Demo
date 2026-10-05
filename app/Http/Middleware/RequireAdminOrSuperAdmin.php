<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Super admin or school_user (admin/principal) can access Administrator routes.
 * Staff cannot access this area.
 */
class RequireAdminOrSuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        $guard = session('auth_guard');

        if (! in_array($guard, ['super_admin', 'school_user'], true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
