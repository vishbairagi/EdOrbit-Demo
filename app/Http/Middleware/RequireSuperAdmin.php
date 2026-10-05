<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Only super_admin can access School Management routes.
 */
class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (session('auth_guard') !== 'super_admin') {
            abort(403, 'Access denied. Super Admin only.');
        }

        return $next($request);
    }
}
