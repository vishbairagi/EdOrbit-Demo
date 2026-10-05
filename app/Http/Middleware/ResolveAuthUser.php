<?php

namespace App\Http\Middleware;

use App\Models\SchoolUser;
use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;

/**
 * Resolves which "user" is logged in (super_admin, school_user, or staff)
 * and stores it in the request for downstream use.
 */
class ResolveAuthUser
{
    public function handle(Request $request, Closure $next): mixed
    {
        $guard = session('auth_guard');
        $userId = session('auth_user_id');

        if (! $guard || ! $userId) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        match ($guard) {
            'super_admin' => $request->attributes->set('auth_user', session('super_admin_data')),
            'school_user' => $request->attributes->set('auth_user', SchoolUser::find($userId)),
            'staff' => $request->attributes->set('auth_user', Staff::find($userId)),
            default => null,
        };

        // Ensure the entity still exists & is active
        $user = $request->attributes->get('auth_user');

        if (! $user) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }

        if ($guard !== 'super_admin' && ! $user->isActive()) {
            session()->flush();

            return redirect()->route('login')
                ->with('error', 'You are inactive for this session in this school. Contact to superadmin.');
        }

        return $next($request);
    }
}
