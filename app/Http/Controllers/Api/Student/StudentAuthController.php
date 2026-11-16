<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class StudentAuthController extends BaseApiController
{
    /**
     * Unified Student Login API.
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
            return $this->error('Username, email, or admission number is required.', 422);
        }

        $student = null;
        $userRecord = null;

        // 1. Check users table for student user account
        $userRecord = User::where('user_type', 'student')
            ->where(function ($q) use ($login) {
                $q->where('username', $login)
                  ->orWhere('email', $login);
            })
            ->first();

        if ($userRecord && Hash::check($password, $userRecord->password)) {
            if ($userRecord->student_id) {
                $student = Student::with(['school', 'session', 'schoolClass', 'section', 'medium', 'studentType'])
                    ->find($userRecord->student_id);
            }
        }

        // 2. Fallback: Check students table by admission_no, email, or phone
        if (! $student) {
            $studentCandidate = Student::with(['school', 'session', 'schoolClass', 'section', 'medium', 'studentType'])
                ->where(function ($q) use ($login) {
                    $q->where('admission_no', $login)
                      ->orWhere('email', $login)
                      ->orWhere('phone', $login);
                })
                ->first();

            if ($studentCandidate) {
                // Check if user record exists for this student
                $linkedUser = User::where('student_id', $studentCandidate->id)->first();
                if ($linkedUser) {
                    if (Hash::check($password, $linkedUser->password)) {
                        $student = $studentCandidate;
                        $userRecord = $linkedUser;
                    }
                } else {
                    // Default password support if no user record yet
                    if (in_array($password, ['123456', 'password', 'password123', 'student123', (string) $studentCandidate->phone], true)) {
                        $student = $studentCandidate;
                        // Create user record for future logins
                        $userRecord = User::create([
                            'name' => $student->full_name,
                            'username' => $student->admission_no,
                            'email' => $student->email ?: $student->admission_no . '@student.edorbit.test',
                            'password' => Hash::make($password),
                            'user_type' => 'student',
                            'student_id' => $student->id,
                        ]);
                    }
                }
            }
        }

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student credentials. Please verify your login and password.',
            ], 401);
        }

        if ($student->status && strtolower($student->status) === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'This student account has been marked inactive. Please contact school administration.',
            ], 403);
        }

        $school = $student->school ?: School::find($student->school_id) ?: School::first();

        // Resolve Active Session
        $requestedSessionId = $request->input('session_id');
        $activeSession = null;
        if ($requestedSessionId) {
            $activeSession = AcademicSession::find($requestedSessionId);
        }

        if (! $activeSession && $student->academic_session_id) {
            $activeSession = AcademicSession::find($student->academic_session_id);
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
            'sub' => (string) $student->id,
            'user_id' => $student->id,
            'student_id' => $student->id,
            'user_type' => 'student',
            'guard' => 'student',
            'school_id' => $school?->id,
            'session_id' => $activeSession?->id,
            'class_id' => $student->school_class_id,
            'section_id' => $student->section_id,
            'user' => [
                'id' => (string) $student->id,
                'type' => 'student',
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
            'logo' => $school?->logo_url ?? '',
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

        // Format Student Profile
        $studentData = $this->formatStudentProfile($student);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => (string) $token,
            'user_type' => 'student',
            'student' => $studentData,
            'school' => $schoolData,
            'active_session' => $this->formatSession($activeSession),
            'sessions' => $sessions,
        ]);
    }

    /**
     * Get Student Profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        if (! $student) {
            return $this->error('Student record not found.', 404);
        }

        $data = $this->formatStudentProfile($student);

        return $this->success($request, $data, 'Student profile retrieved successfully');
    }

    /**
     * Update Student Profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        if (! $student) {
            return $this->error('Student record not found.', 404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students/photos', 'public');
            $validated['photo'] = $path;
        }

        $student->update($validated);

        return $this->success($request, $this->formatStudentProfile($student->fresh()), 'Profile updated successfully');
    }

    /**
     * Student Account Settings (Change Password).
     */
    public function accountSettings(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        if (! $student) {
            return $this->error('Student record not found.', 404);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6',
        ]);

        $user = User::where('student_id', $student->id)->first();
        if (! $user) {
            $user = User::create([
                'name' => $student->full_name,
                'username' => $student->admission_no,
                'email' => $student->email ?: $student->admission_no . '@student.edorbit.test',
                'password' => Hash::make($validated['current_password']),
                'user_type' => 'student',
                'student_id' => $student->id,
            ]);
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password does not match our records.', 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->success($request, null, 'Account password updated successfully');
    }

    /**
     * Switch Academic Session for Student.
     */
    public function switchSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $student = $this->getStudent($request);
        $newSession = AcademicSession::findOrFail($validated['session_id']);

        $customClaims = [
            'sub' => (string) $student->id,
            'user_id' => $student->id,
            'student_id' => $student->id,
            'user_type' => 'student',
            'guard' => 'student',
            'school_id' => $student->school_id,
            'session_id' => $newSession->id,
            'class_id' => $student->school_class_id,
            'section_id' => $student->section_id,
            'user' => [
                'id' => (string) $student->id,
                'type' => 'student',
            ],
        ];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();

        return response()->json([
            'success' => true,
            'message' => "Academic session switched to {$newSession->name}",
            'token' => (string) $token,
            'active_session' => $this->formatSession($newSession),
        ]);
    }

    /**
     * Helper to format student profile details.
     */
    private function formatStudentProfile(Student $student): array
    {
        $dob = $student->date_of_birth instanceof \DateTimeInterface
            ? $student->date_of_birth->format('Y-m-d')
            : (string) $student->date_of_birth;

        return [
            'id' => $student->id,
            'admission_no' => (string) $student->admission_no,
            'roll_number' => (string) ($student->roll_number ?? ''),
            'first_name' => (string) $student->first_name,
            'middle_name' => (string) ($student->middle_name ?? ''),
            'last_name' => (string) $student->last_name,
            'full_name' => $student->full_name,
            'gender' => (string) $student->gender,
            'date_of_birth' => $dob,
            'blood_group' => (string) ($student->blood_group ?? ''),
            'religion' => (string) ($student->religion ?? ''),
            'caste' => (string) ($student->caste ?? ''),
            'category' => (string) ($student->category ?? ''),
            'phone' => (string) ($student->phone ?? ''),
            'email' => (string) ($student->email ?? ''),
            'address' => (string) ($student->address ?? ''),
            'city' => (string) ($student->city ?? ''),
            'state' => (string) ($student->state ?? ''),
            'country' => (string) ($student->country ?? ''),
            'photo' => $student->photo_url ?? '',
            'status' => (string) $student->status,
            'class' => [
                'id' => $student->school_class_id,
                'name' => (string) ($student->schoolClass?->name ?? ''),
            ],
            'section' => [
                'id' => $student->section_id,
                'name' => (string) ($student->section?->name ?? ''),
            ],
            'medium' => [
                'id' => $student->medium_id,
                'name' => (string) ($student->medium?->name ?? ''),
            ],
            'parent_details' => [
                'father_name' => (string) ($student->father_name ?? ''),
                'father_phone' => (string) ($student->father_phone ?? ''),
                'father_occupation' => (string) ($student->father_occupation ?? ''),
                'mother_name' => (string) ($student->mother_name ?? ''),
                'mother_phone' => (string) ($student->mother_phone ?? ''),
                'mother_occupation' => (string) ($student->mother_occupation ?? ''),
                'guardian_name' => (string) ($student->guardian_name ?? ''),
                'guardian_phone' => (string) ($student->guardian_phone ?? ''),
                'guardian_relation' => (string) ($student->guardian_relation ?? ''),
                'guardian_address' => (string) ($student->guardian_address ?? ''),
            ],
        ];
    }
}
