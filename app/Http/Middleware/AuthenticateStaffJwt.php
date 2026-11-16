<?php

namespace App\Http\Middleware;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateStaffJwt
{
    /**
     * Handle incoming request and authenticate Teacher or Admin via JWT Bearer token.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            $userId = $payload->get('sub') ?? ($payload->get('user')['id'] ?? null);
            $userType = $payload->get('user_type') ?? 'teacher';
            $guard = $payload->get('guard');
            $tokenSchoolId = $payload->get('school_id');

            if (! $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token: User ID not found in token claims.',
                ], 401);
            }

            // Resolve User based on user_type or guard
            if ($userType === 'admin' || $guard === 'school_user') {
                $user = SchoolUser::with('school')->find($userId);

                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Admin user record not found for this token.',
                    ], 404);
                }

                if (! $user->isActive()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This admin account is inactive for this school.',
                    ], 403);
                }

                $userType = 'admin';
                $schoolId = $user->school_id;
            } elseif ($userType === 'super_admin' || $guard === 'super_admin') {
                // Super Admin
                $schoolId = $request->header('X-School-ID')
                    ?: $request->input('school_id', $tokenSchoolId ?: School::value('id'));

                $user = (object) [
                    'id' => 0,
                    'name' => 'Super Admin',
                    'username' => 'super_admin',
                    'email' => 'superadmin@edorbit.test',
                    'role' => 'super_admin',
                    'school_id' => $schoolId,
                ];
                $userType = 'super_admin';
            } else {
                // Teacher / Staff
                $user = Staff::with(['school', 'role.permissions'])->find($userId);

                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Teacher/Staff record not found for this token.',
                    ], 404);
                }

                if (! $user->isActive()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This teacher account is inactive for this school.',
                    ], 403);
                }

                $userType = 'teacher';
                $schoolId = $user->school_id;
            }

            // Ensure valid schoolId exists
            if (! $schoolId || ! School::where('id', $schoolId)->exists()) {
                $schoolId = School::value('id');
            }

            // Resolve Session: Check header X-Session-ID, query param, body param, token claim, or system default
            $requestedSessionId = $request->header('X-Session-ID')
                ?: $request->input('session_id', $payload->get('session_id'));

            $activeSession = null;
            if ($requestedSessionId) {
                $activeSession = AcademicSession::find($requestedSessionId);
            }

            if (! $activeSession) {
                $defaultSessionId = SystemSetting::getSetting('current_session_id');
                if ($defaultSessionId) {
                    $activeSession = AcademicSession::find($defaultSessionId);
                }
            }

            if (! $activeSession) {
                $activeSession = AcademicSession::where('status', 'Active')
                    ->orWhere('status', 'active')
                    ->latest('id')
                    ->first() ?: AcademicSession::latest('id')->first();
            }

            // Store resolved attributes on request
            $request->attributes->set('auth_user', $user);
            $request->attributes->set('user_type', $userType);
            $request->attributes->set('school_id', $schoolId);
            $request->attributes->set('active_session', $activeSession);
            $request->attributes->set('session_id', $activeSession?->id);

            // Also keep session() in sync for any internal Eloquent model methods
            session([
                'auth_guard' => ($userType === 'admin') ? 'school_user' : (($userType === 'super_admin') ? 'super_admin' : 'staff'),
                'auth_user_id' => $user->id ?? 0,
                'selected_school_id' => $schoolId,
                'selected_session_id' => $activeSession?->id,
                'selected_academic_session_id' => $activeSession?->id,
            ]);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired. Please login again.',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid.',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Bearer token is required.',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
