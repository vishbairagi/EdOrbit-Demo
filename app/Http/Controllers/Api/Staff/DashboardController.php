<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\SmRoleController;
use App\Models\ClassTeacher;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    /**
     * Staff & Admin Dashboard API.
     */
    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);

        $schoolId = $school?->id;
        $sessionId = $session?->id;

        // 1. Calculate Permissions Map (true / false for each permission)
        $isAdmin = in_array($userType, ['admin', 'super_admin'], true);
        $permissionsFlat = [];
        $permissionsGrouped = [];

        $isClassTeacher = false;
        if (! $isAdmin && $user instanceof Staff && $schoolId && $sessionId) {
            $isClassTeacher = ClassTeacher::where('staff_id', $user->id)
                ->where('school_id', $schoolId)
                ->where('session_id', $sessionId)
                ->exists();
        }

        $classTeacherAllowedKeys = [
            'sm-student.students',
            'sm-student.print-id-cards',
            'academic.class-sections',
            'academic.subjects',
            'academic.study-materials',
            'academic.noticeboards',
            'examination.admit-cards',
            'examination.results',
        ];

        foreach (SmRoleController::$availablePermissions as $group => $perms) {
            $permissionsGrouped[$group] = [];
            foreach ($perms as $key => $label) {
                if ($isAdmin) {
                    $has = true;
                } else {
                    $has = ($user instanceof Staff) && $user->hasPermission($key);
                    if (! $has && $isClassTeacher && in_array($key, $classTeacherAllowedKeys, true)) {
                        $has = true;
                    }
                }
                $permissionsFlat[$key] = (bool) $has;
                $permissionsGrouped[$group][$key] = [
                    'label' => $label,
                    'allowed' => (bool) $has,
                ];
            }
        }

        // 2. Personal Details
        $photoUrl = $user->photo ?? '';
        if ($photoUrl && ! filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $photoUrl = asset('storage/' . ltrim($photoUrl, '/'));
        }

        $userRole = (is_object($user) && (property_exists($user, 'role') || isset($user->role))) ? $user->role : null;
        $roleName = $userRole instanceof \App\Models\StaffRole ? $userRole->name : (is_string($userRole) && $userRole !== '' ? $userRole : ucfirst($userType ?? 'staff'));
        $designation = $user->designation ?? ($roleName ?: ucfirst($userType ?? 'staff'));

        $personalDetails = [
            'id' => $user->id ?? 0,
            'name' => $user->name ?? '',
            'username' => $user->username ?? '',
            'email' => $user->email ?? ($user->login_email ?? ''),
            'phone' => $user->phone ?? ($user->login_phone ?? ''),
            'gender' => $user->gender ?? '',
            'employee_id' => $user->employee_id ?? '',
            'designation' => $designation,
            'photo' => $photoUrl,
            'address' => $user->address ?? '',
            'role_name' => $roleName,
            'is_class_teacher' => $isClassTeacher,
        ];

        // 3. School Details
        $schoolDetails = [
            'id' => $school?->id,
            'name' => (string) ($school?->name ?? ''),
            'phone' => (string) ($school?->phone ?? ''),
            'email' => (string) ($school?->email ?? ''),
            'address' => (string) ($school?->address ?? ''),
            'code' => (string) ($school?->code ?? ''),
            'logo' => $school?->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
        ];

        // 4. Statistics
        $totalStudents = 0;
        $totalClasses = 0;
        $totalSections = 0;
        $totalSubjects = 0;
        $totalStaff = 0;
        $totalFeeInvoiced = 0;
        $totalFeeCollected = 0;

        if ($schoolId && $sessionId) {
            $totalStudents = Student::where('school_id', $schoolId)
                ->where('academic_session_id', $sessionId)
                ->count();

            $totalClasses = SchoolClass::where('school_id', $schoolId)
                ->where('academic_session_id', $sessionId)
                ->count();

            $totalSections = Section::whereHas('schoolClass', function ($q) use ($schoolId, $sessionId) {
                $q->where('school_id', $schoolId)->where('academic_session_id', $sessionId);
            })->count();

            $totalSubjects = Subject::where('school_id', $schoolId)
                ->where('academic_session_id', $sessionId)
                ->count();

            $totalStaff = Staff::where('school_id', $schoolId)
                ->where('status', 'Active')
                ->count();

            $totalFeeInvoiced = (float) FeeInvoice::where('school_id', $schoolId)
                ->where('academic_session_id', $sessionId)
                ->sum('total_amount');

            $totalFeeCollected = (float) FeePayment::whereHas('invoice', function ($q) use ($schoolId, $sessionId) {
                $q->where('school_id', $schoolId)->where('academic_session_id', $sessionId);
            })->sum('amount');
        }

        $statistics = [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'total_sections' => $totalSections,
            'total_subjects' => $totalSubjects,
            'total_staff' => $totalStaff,
            'finance' => [
                'total_invoiced' => $totalFeeInvoiced,
                'total_collected' => $totalFeeCollected,
                'due_amount' => max(0, $totalFeeInvoiced - $totalFeeCollected),
            ],
        ];

        return $this->success($request, [
            'school' => $schoolDetails,
            'teacher_details' => $personalDetails,
            'user_type' => $userType,
            'permissions' => $permissionsFlat,
            'permissions_grouped' => $permissionsGrouped,
            'statistics' => $statistics,
        ], 'Dashboard details retrieved successfully');
    }

    /**
     * All permissions list (for Postman endpoint /sm/staff/all-permission-list).
     */
    public function allPermissions(Request $request): JsonResponse
    {
        $permissions = SmRoleController::$availablePermissions;

        return $this->success($request, $permissions, 'All permissions list');
    }
}
