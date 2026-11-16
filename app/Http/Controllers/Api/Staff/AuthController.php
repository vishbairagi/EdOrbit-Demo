<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends BaseApiController
{
    /**
     * Unified Login API for Teacher and Admin (and optionally Student / SuperAdmin).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'nullable|string',
            'login'    => 'nullable|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('username') ?? $request->input('login') ?? '');
        $password = $request->input('password');

        if (empty($login)) {
            return $this->error('Username or email is required.', 422);
        }

        $user = null;
        $userType = null;
        $guard = null;
        $school = null;

        // 1. Check Super Admin (from config)
        if ($login === config('auth.super_admin_username') &&
            Hash::check($password, config('auth.super_admin_password_hash'))) {
            $userType = 'super_admin';
            $guard = 'super_admin';
            $schoolId = $request->input('school_id') ?: School::value('id');
            $school = School::find($schoolId);
            $user = (object) [
                'id' => 0,
                'name' => 'Super Admin',
                'username' => 'super_admin',
                'email' => 'superadmin@edorbit.test',
                'phone' => '',
                'employee_id' => 'SA-0001',
                'designation' => 'Super Administrator',
                'photo' => '',
                'gender' => 'Male',
                'school_id' => $schoolId,
                'role' => 'Super Administrator',
            ];
        }

        // 2. Check Admin (SchoolUser)
        if (! $user) {
            $schoolUser = SchoolUser::with('school')
                ->where(function ($q) use ($login) {
                    $q->where('username', $login)
                      ->orWhere('login_email', $login)
                      ->orWhere('login_phone', $login)
                      ->orWhere('email', $login);
                })
                ->first();

            if ($schoolUser && Hash::check($password, $schoolUser->password)) {
                if (! $schoolUser->isActive()) {
                    return $this->error('You are inactive for this school. Please contact administration.', 403);
                }
                $user = $schoolUser;
                $userType = 'admin';
                $guard = 'school_user';
                $school = $schoolUser->school;
            }
        }

        // 3. Check Teacher / Staff
        if (! $user) {
            $staff = Staff::with(['school', 'role'])
                ->where(function ($q) use ($login) {
                    $q->where('username', $login)
                      ->orWhere('login_email', $login)
                      ->orWhere('login_phone', $login)
                      ->orWhere('email', $login);
                })
                ->first();

            if ($staff && Hash::check($password, $staff->password)) {
                if (! $staff->isActive()) {
                    return $this->error('You are inactive for this school. Please contact administration.', 403);
                }
                $user = $staff;
                $userType = 'teacher';
                $guard = 'staff';
                $school = $staff->school;
            }
        }

        // 4. Check Student (via users table)
        if (! $user) {
            $studentUser = DB::table('users')
                ->where('user_type', 'student')
                ->where(function ($q) use ($login) {
                    $q->where('username', $login)->orWhere('email', $login);
                })
                ->first();

            if ($studentUser && Hash::check($password, $studentUser->password)) {
                $student = \App\Models\Student::with(['school'])->find($studentUser->student_id);
                if ($student) {
                    $user = $student;
                    $userType = 'student';
                    $guard = 'student';
                    $school = $student->school;
                }
            }
        }

        if (! $user) {
            return $this->error('Invalid credentials. Please verify your login and password.', 401);
        }

        if (! $school) {
            $school = ($user->school_id ? School::find($user->school_id) : null) ?: School::first();
        }

        // Resolve Active Session
        $requestedSessionId = $request->input('session_id');
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

        // Generate JWT Token
        $customClaims = [
            'sub' => (string) $user->id,
            'user_id' => $user->id,
            'user_type' => $userType,
            'guard' => $guard,
            'school_id' => $school?->id ?? ($user->school_id ?? null),
            'session_id' => $activeSession?->id,
            'user' => [
                'id' => (string) $user->id,
                'type' => $userType,
            ],
        ];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();

        // Format School
        $schoolData = [
            'id' => $school?->id,
            'name' => (string) ($school?->name ?? ''),
            'phone' => (string) ($school?->phone ?? ''),
            'email' => (string) ($school?->email ?? ''),
            'address' => (string) ($school?->address ?? ''),
            'logo' => $school?->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
        ];

        // Format All Sessions List
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get()->map(function ($s) use ($activeSession) {
            $startDate = $s->start_date instanceof \DateTimeInterface ? $s->start_date->format('Y-m-d') : (string) $s->start_date;
            $endDate = $s->end_date instanceof \DateTimeInterface ? $s->end_date->format('Y-m-d') : (string) $s->end_date;

            return [
                'id' => $s->id,
                'name' => $s->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $s->status,
                'is_active' => $activeSession && $activeSession->id === $s->id,
            ];
        });

        // Format Profile
        $photoUrl = $user->photo ?? '';
        if ($photoUrl && ! filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $photoUrl = asset('storage/' . ltrim($photoUrl, '/'));
        }

        $userRole = (is_object($user) && (property_exists($user, 'role') || isset($user->role))) ? $user->role : null;
        $roleName = $userRole instanceof \App\Models\StaffRole ? $userRole->name : (is_string($userRole) && $userRole !== '' ? $userRole : ucfirst($userType ?? 'staff'));
        $designation = $user->designation ?? ($roleName ?: ($userType === 'admin' ? 'Administrator' : ($userType === 'student' ? 'Student' : 'Teacher')));

        $profile = [
            'id' => $user->id ?? 0,
            'name' => $user->name ?? '',
            'username' => $user->username ?? '',
            'email' => $user->email ?? ($user->login_email ?? ''),
            'phone' => $user->phone ?? ($user->login_phone ?? ''),
            'gender' => $user->gender ?? '',
            'employee_id' => $user->employee_id ?? '',
            'designation' => $designation,
            'photo' => $photoUrl,
            'role_name' => $roleName,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'token' => $token,
            'user_type' => $userType,
            'user_email' => $profile['email'],
            'user_nicename' => $profile['name'],
            'user_display_name' => $profile['name'],
            'photo' => $photoUrl,
            'school' => $schoolData,
            'active_session' => $this->formatSession($activeSession),
            'sessions' => $sessions,
            'profile' => $profile,
        ]);
    }

    /**
     * Get Logged-in User Profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);
        $school = $this->getSchool($request);

        $photoUrl = $user->photo ?? '';
        if ($photoUrl && ! filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $photoUrl = asset('storage/' . ltrim($photoUrl, '/'));
        }

        $userRole = (is_object($user) && (property_exists($user, 'role') || isset($user->role))) ? $user->role : null;
        $roleName = $userRole instanceof \App\Models\StaffRole ? $userRole->name : (is_string($userRole) && $userRole !== '' ? $userRole : ucfirst($userType ?? 'staff'));
        $designation = $user->designation ?? ($roleName ?: ucfirst($userType ?? 'staff'));

        $profile = [
            'id' => $user->id ?? 0,
            'name' => $user->name ?? '',
            'username' => $user->username ?? '',
            'email' => $user->email ?? ($user->login_email ?? ''),
            'phone' => $user->phone ?? ($user->login_phone ?? ''),
            'gender' => $user->gender ?? '',
            'employee_id' => $user->employee_id ?? '',
            'designation' => $designation,
            'photo' => $photoUrl,
            'date_of_birth' => isset($user->date_of_birth) && $user->date_of_birth instanceof \DateTimeInterface ? $user->date_of_birth->format('Y-m-d') : ($user->date_of_birth ?? null),
            'joining_date' => isset($user->joining_date) && $user->joining_date instanceof \DateTimeInterface ? $user->joining_date->format('Y-m-d') : ($user->joining_date ?? null),
            'address' => $user->address ?? '',
            'role_name' => $roleName,
        ];

        return $this->success($request, [
            'user_type' => $userType,
            'profile' => $profile,
            'school' => [
                'id' => $school?->id,
                'name' => $school?->name ?? '',
                'phone' => $school?->phone ?? '',
                'email' => $school?->email ?? '',
                'address' => $school?->address ?? '',
                'logo' => $school?->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
            ],
        ], 'Profile retrieved successfully');
    }

    /**
     * Switch Active Session dynamically and obtain new token.
     */
    public function switchSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $session = AcademicSession::findOrFail($request->session_id);
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);
        $school = $this->getSchool($request);

        // Re-generate JWT with new session_id claim
        $customClaims = [
            'sub' => (string) $user->id,
            'user_id' => $user->id,
            'user_type' => $userType,
            'guard' => $userType === 'admin' ? 'school_user' : ($userType === 'super_admin' ? 'super_admin' : 'staff'),
            'school_id' => $school?->id,
            'session_id' => $session->id,
            'user' => [
                'id' => (string) $user->id,
                'type' => $userType,
            ],
        ];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();

        // Update request attribute for this lifecycle
        $request->attributes->set('active_session', $session);
        $request->attributes->set('session_id', $session->id);

        return $this->success($request, [
            'token' => $token,
            'active_session' => $this->formatSession($session),
        ], "Active session successfully switched to {$session->name}");
    }

    /**
     * Get list of all academic sessions.
     */
    public function sessionList(Request $request): JsonResponse
    {
        $activeSession = $this->getSession($request);

        $sessions = AcademicSession::orderBy('start_date', 'desc')->get()->map(function ($s) use ($activeSession) {
            $startDate = $s->start_date instanceof \DateTimeInterface ? $s->start_date->format('Y-m-d') : (string) $s->start_date;
            $endDate = $s->end_date instanceof \DateTimeInterface ? $s->end_date->format('Y-m-d') : (string) $s->end_date;

            return [
                'id' => $s->id,
                'name' => $s->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $s->status,
                'is_active' => $activeSession && $activeSession->id === $s->id,
            ];
        });

        return $this->success($request, $sessions, 'Academic session list retrieved');
    }

    /**
     * Get active school session details.
     */
    public function schoolSession(Request $request): JsonResponse
    {
        $session = $this->getSession($request);

        return $this->success($request, $this->formatSession($session), 'Active school session retrieved');
    }

    /**
     * Currency List endpoint (for Postman/frontend currency options).
     */
    public function currencyList(Request $request): JsonResponse
    {
        $currencies = [
            ['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupee'],
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
            ['code' => 'AED', 'symbol' => 'د.إ', 'name' => 'UAE Dirham'],
            ['code' => 'SAR', 'symbol' => '﷼', 'name' => 'Saudi Riyal'],
        ];

        return $this->success($request, $currencies, 'Currency list retrieved');
    }

    /**
     * Switch School (for multi-school admins / staff).
     */
    public function switchSchool(Request $request): JsonResponse
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = School::findOrFail($request->school_id);
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);
        $session = $this->getSession($request);

        $customClaims = [
            'sub' => (string) $user->id,
            'user_id' => $user->id,
            'user_type' => $userType,
            'guard' => $userType === 'admin' ? 'school_user' : 'staff',
            'school_id' => $school->id,
            'session_id' => $session?->id,
            'user' => [
                'id' => (string) $user->id,
                'type' => $userType,
            ],
        ];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();

        return $this->success($request, [
            'token' => $token,
            'school_id' => $school->id,
            'school_name' => $school->name,
        ], "Switched to school {$school->name}");
    }
}
