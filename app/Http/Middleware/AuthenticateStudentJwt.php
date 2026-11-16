<?php

namespace App\Http\Middleware;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateStudentJwt
{
    /**
     * Handle incoming request and authenticate Student via JWT Bearer token.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $bearerToken = $request->bearerToken();
            if (! $bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bearer token is missing or unauthorized. Please provide a valid student JWT Bearer token in the Authorization header.',
                    'error_code' => 'TOKEN_MISSING',
                ], 401);
            }

            $token = JWTAuth::setToken($bearerToken);
            $payload = $token->getPayload();

            $userType = $payload->get('user_type') ?? 'student';
            $studentId = $payload->get('student_id') ?? $payload->get('sub') ?? ($payload->get('user')['id'] ?? null);

            if (! $studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token: Student ID not found in token claims.',
                ], 401);
            }

            if (! in_array($userType, ['student', 'parent'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: Token is not for a student account.',
                ], 403);
            }

            $student = Student::with(['school', 'session', 'schoolClass', 'section', 'medium', 'studentType'])->find($studentId);

            if (! $student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found for this token.',
                ], 404);
            }

            if ($student->status && strtolower($student->status) === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'This student account has been marked inactive. Please contact school administration.',
                ], 403);
            }

            $school = $student->school ?: School::find($student->school_id) ?: School::first();

            // Resolve Dynamic Academic Session
            $requestedSessionId = $request->header('X-Session-ID')
                ?: $request->query('session_id')
                ?: $request->input('session_id')
                ?: $payload->get('session_id')
                ?: $student->academic_session_id;

            $session = null;
            if ($requestedSessionId) {
                $session = AcademicSession::find($requestedSessionId);
            }

            if (! $session) {
                $defaultSessionId = SystemSetting::getSetting('current_session_id');
                if ($defaultSessionId) {
                    $session = AcademicSession::find($defaultSessionId);
                }
            }

            if (! $session) {
                $session = AcademicSession::where('status', 'Active')
                    ->orWhere('status', 'active')
                    ->latest('id')
                    ->first() ?: AcademicSession::latest('id')->first();
            }

            // Inject attributes into request for controllers
            $request->attributes->set('student', $student);
            $request->attributes->set('student_id', $student->id);
            $request->attributes->set('school', $school);
            $request->attributes->set('session', $session);
            $request->attributes->set('user_type', 'student');
            $request->attributes->set('jwt_payload', $payload->toArray());

            return $next($request);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Your student session token has expired. Please login again.',
                'error_code' => 'TOKEN_EXPIRED',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Your student session token is invalid.',
                'error_code' => 'TOKEN_INVALID',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bearer token is missing or unauthorized. Please provide a valid student JWT Bearer token in the Authorization header.',
                'error_code' => 'TOKEN_MISSING',
            ], 401);
        }
    }
}
