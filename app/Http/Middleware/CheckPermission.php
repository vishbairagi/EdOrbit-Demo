<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SmRoleController;
use App\Models\ClassTeacher;
use App\Models\Staff;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $guard = session('auth_guard');

        // Super admins have all permissions
        if ($guard === 'super_admin') {
            return $next($request);
        }

        // Admin/Principals have all permissions for their school
        if ($guard === 'school_user') {
            return $next($request);
        }

        $userId = session('auth_user_id');
        if (! $guard || ! $userId) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        if ($guard === 'staff') {
            $user = Staff::find($userId);
        } else {
            $user = null;
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // Get the current route name
        $routeName = $request->route()->getName();

        if (! $routeName) {
            return $next($request);
        }

        // Available permission keys from SmRoleController
        $availableKeys = [];
        $permissionsArray = SmRoleController::$availablePermissions;
        foreach ($permissionsArray as $group => $perms) {
            foreach ($perms as $key => $label) {
                $availableKeys[] = $key;
            }
        }

        // Sort keys by length descending to match the most specific permission first
        usort($availableKeys, fn ($a, $b) => strlen($b) <=> strlen($a));

        // Find which permission key applies to this route.
        // E.g. route 'academic.subjects.index' -> matches 'academic.subjects'
        $requiredKey = null;
        foreach ($availableKeys as $key) {
            if ($routeName === $key || Str::startsWith($routeName, $key.'.')) {
                $requiredKey = $key;
                break;
            }
        }

        // If the route maps to a permission key, check if the user has it
        if ($requiredKey) {
            if ($user instanceof Staff) {
                $hasExplicitPerm = $user->hasPermission($requiredKey);
                $isAllowedByClassTeacher = false;
                $isClassTeacher = false;

                if (! $hasExplicitPerm || in_array($requiredKey, ['academic.class-teachers', 'academic.subjects'], true)) {
                    $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
                    $isClassTeacher = ClassTeacher::where('staff_id', $user->id)
                        ->where('school_id', session('selected_school_id'))
                        ->where('session_id', $sessionId)
                        ->exists();
                }

                if ($requiredKey === 'academic.class-teachers' && $isClassTeacher) {
                    $hasExplicitPerm = false;
                }

                if ($requiredKey === 'academic.subjects' && $isClassTeacher) {
                    $hasExplicitPerm = in_array($routeName, ['academic.subjects.index', 'academic.subjects.store'], true);
                }

                if (! $hasExplicitPerm && $isClassTeacher) {
                    $allowedRoutesForClassTeacher = [
                        'sm-student.students.index',
                        'sm-student.students.show',
                        'sm-student.print-id-cards.index',
                        'sm-student.print-id-cards.print',
                        'academic.subjects.index',
                        'academic.subjects.store',
                    ];

                    if (in_array($routeName, $allowedRoutesForClassTeacher)) {
                        $isAllowedByClassTeacher = true;
                    }
                }

                if (! $hasExplicitPerm && ! $isAllowedByClassTeacher) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['error' => 'You do not have permission to access this page.'], 403);
                    }
                    // Check if coming from a different page, else redirect to dashboard
                    $url = url()->previous();
                    if ($url === url()->current() || $url === url('/')) {
                        return redirect()->route('staff.dashboard')->with('error', 'You do not have permission to access this page.');
                    }

                    return back()->with('error', 'You do not have permission to access this page.');
                }
            }
        }

        return $next($request);
    }
}
