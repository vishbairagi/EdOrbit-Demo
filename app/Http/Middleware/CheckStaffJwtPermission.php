<?php

namespace App\Http\Middleware;

use App\Models\ClassTeacher;
use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;

class CheckStaffJwtPermission
{
    /**
     * Handle incoming request and verify user's permission for the given key.
     */
    public function handle(Request $request, Closure $next, string $permissionKey)
    {
        $userType = $request->attributes->get('user_type');
        $user = $request->attributes->get('auth_user');

        // Super Admin & Admin (SchoolUser/Principal) have full access to their school
        if ($userType === 'super_admin' || $userType === 'admin') {
            return $next($request);
        }

        // Staff / Teacher permission check
        if ($user instanceof Staff) {
            $hasPermission = $user->hasPermission($permissionKey);

            if (! $hasPermission) {
                // Check if allowed via Class Teacher role
                $schoolId = $request->attributes->get('school_id');
                $sessionId = $request->attributes->get('session_id');

                $isClassTeacher = ClassTeacher::where('staff_id', $user->id)
                    ->where('school_id', $schoolId)
                    ->where('session_id', $sessionId)
                    ->exists();

                if ($isClassTeacher) {
                    $allowedForClassTeacher = [
                        'sm-student.students',
                        'sm-student.print-id-cards',
                        'academic.class-sections',
                        'academic.subjects',
                        'academic.study-materials',
                        'academic.noticeboards',
                        'examination.admit-cards',
                        'examination.results',
                    ];

                    if (in_array($permissionKey, $allowedForClassTeacher, true)) {
                        $hasPermission = true;
                    }
                }
            }

            if ($hasPermission) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Permission denied. You do not have permission for: {$permissionKey}",
        ], 403);
    }
}
