<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentApiController extends BaseApiController
{
    // ==========================================
    // 1. STUDENTS CRUD (sm-student.students)
    // ==========================================

    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);
        $user = $this->getUser($request);
        $userType = $this->getUserType($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $query = Student::with(['schoolClass', 'section', 'medium'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        // If class teacher with limited permissions
        if ($userType === 'teacher' && $user instanceof Staff && ! $user->hasPermission('sm-student.students')) {
            $allowedClassIds = ClassTeacher::where('staff_id', $user->id)
                ->where('school_id', $school->id)
                ->where('session_id', $session->id)
                ->pluck('class_id')
                ->toArray();
            $query->whereIn('school_class_id', $allowedClassIds);
        }

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($sectionId = $request->input('section_id')) {
            $query->where('section_id', $sectionId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('first_name')->get(), 'Students retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->paginated($request, $query->orderBy('first_name')->paginate($perPage), 'Students retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'school_class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'medium_id' => 'nullable|exists:mediums,id',
            'gender' => 'required|in:Male,Female,Other,male,female,other',
            'date_of_birth' => 'nullable|date',
            'admission_no' => 'nullable|string|max:50',
            'roll_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'caste' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:100',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:20',
            'photo' => 'nullable|file|image|max:5120',
            'photo_url' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        // Auto-generate admission_no if omitted
        $admissionNo = $validated['admission_no'] ?? null;
        if (! $admissionNo) {
            $count = Student::where('school_id', $school->id)->count() + 1;
            $admissionNo = 'ADM-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $photoPath = $validated['photo_url'] ?? null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'admission_no' => $admissionNo,
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'medium_id' => $validated['medium_id'] ?? null,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'gender' => ucfirst(strtolower($validated['gender'])),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'roll_number' => $validated['roll_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'caste' => $validated['caste'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'father_phone' => $validated['father_phone'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'mother_phone' => $validated['mother_phone'] ?? null,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'photo' => $photoPath,
            'status' => $validated['status'] ?? 'Active',
        ]);

        $student->load(['schoolClass', 'section', 'medium']);

        return $this->success($request, $student, 'Student enrolled successfully', 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $student = Student::with(['schoolClass', 'section', 'medium'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $student) {
            return $this->error('Student not found.', 404);
        }

        return $this->success($request, $student, 'Student details retrieved');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $student = Student::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $student) {
            return $this->error('Student not found.', 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'school_class_id' => 'sometimes|required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'medium_id' => 'nullable|exists:mediums,id',
            'gender' => 'nullable|in:Male,Female,Other,male,female,other',
            'date_of_birth' => 'nullable|date',
            'roll_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:100',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'nullable|string|max:100',
            'photo' => 'nullable|file|image|max:5120',
            'photo_url' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students', 'public');
        } elseif (isset($validated['photo_url'])) {
            $validated['photo'] = $validated['photo_url'];
        }

        if (isset($validated['gender'])) {
            $validated['gender'] = ucfirst(strtolower($validated['gender']));
        }

        $student->update($validated);
        $student->load(['schoolClass', 'section', 'medium']);

        return $this->success($request, $student, 'Student updated successfully');
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $student = Student::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $student) {
            return $this->error('Student not found.', 404);
        }

        $student->delete();

        return $this->success($request, null, 'Student removed successfully');
    }

    // ==========================================
    // 2. ID CARDS (sm-student.print-id-cards)
    // ==========================================

    public function idCards(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = Student::with(['schoolClass', 'section', 'school'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->where('status', 'Active');

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($sectionId = $request->input('section_id')) {
            $query->where('section_id', $sectionId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('admission_no')->get()->map(function ($s) use ($school, $session) {
            return [
                'student_id' => $s->id,
                'full_name' => $s->full_name,
                'admission_no' => $s->admission_no,
                'roll_number' => $s->roll_number,
                'class' => $s->schoolClass?->name ?? '',
                'section' => $s->section?->name ?? '',
                'date_of_birth' => $s->date_of_birth instanceof \DateTimeInterface ? $s->date_of_birth->format('d-m-Y') : (string) $s->date_of_birth,
                'blood_group' => $s->blood_group,
                'father_name' => $s->father_name,
                'phone' => $s->phone ?? $s->father_phone,
                'address' => $s->address,
                'photo_url' => $s->photo_url,
                'school_name' => $school?->name ?? '',
                'school_phone' => $school?->phone ?? '',
                'school_address' => $school?->address ?? '',
                'school_logo' => $school?->logo ? (filter_var($school->logo, FILTER_VALIDATE_URL) ? $school->logo : asset('storage/' . ltrim($school->logo, '/'))) : '',
                'session_name' => $session?->name ?? '',
            ];
        });

        return $this->success($request, $students, 'ID Cards data retrieved successfully');
    }

    // ==========================================
    // 3. PROMOTE STUDENTS (sm-student.promote)
    // ==========================================

    public function promote(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id' => 'required|exists:classes,id',
            'to_section_id' => 'nullable|exists:sections,id',
        ]);

        $toSession = AcademicSession::findOrFail($validated['to_session_id']);
        $toClass = SchoolClass::findOrFail($validated['to_class_id']);
        $toSectionId = $validated['to_section_id'] ?? null;

        $promotedCount = 0;
        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::where('school_id', $school->id)->find($studentId);
            if (! $student) {
                continue;
            }

            // Check if student already enrolled in target session
            $existing = Student::where('school_id', $school->id)
                ->where('admission_no', $student->admission_no)
                ->where('academic_session_id', $toSession->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'school_class_id' => $toClass->id,
                    'section_id' => $toSectionId,
                    'status' => 'Active',
                ]);
            } else {
                $newStudent = $student->replicate(['academic_session_id', 'school_class_id', 'section_id', 'status', 'roll_number']);
                $newStudent->academic_session_id = $toSession->id;
                $newStudent->school_class_id = $toClass->id;
                $newStudent->section_id = $toSectionId;
                $newStudent->status = 'Active';
                $newStudent->roll_number = null;
                $newStudent->admission_no = $student->admission_no;
                $newStudent->save();
            }

            $promotedCount++;
        }

        return $this->success($request, [
            'promoted_count' => $promotedCount,
            'target_session' => $toSession->name,
            'target_class' => $toClass->name,
        ], "{$promotedCount} student(s) promoted successfully to {$toSession->name}");
    }

    // ==========================================
    // 4. TRANSFER STUDENTS (sm-student.transfer)
    // ==========================================

    public function transfer(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'transfer_reason' => 'nullable|string|max:500',
            'to_school_name' => 'nullable|string|max:255',
            'leaving_certificate_no' => 'nullable|string|max:100',
        ]);

        $student = Student::where('school_id', $school?->id)->findOrFail($validated['student_id']);

        $student->update([
            'status' => 'Transferred',
            'leaving_certificate_no' => $validated['leaving_certificate_no'] ?? ('TC-' . date('Y') . '-' . $student->id),
            'extra_note' => $validated['transfer_reason'] ?? $student->extra_note,
        ]);

        return $this->success($request, $student->load(['schoolClass', 'section']), 'Student transferred successfully');
    }

    public function transferList(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $transferredStudents = Student::with(['schoolClass', 'section', 'session'])
            ->where('school_id', $school?->id)
            ->where('status', 'Transferred')
            ->latest('updated_at')
            ->get();

        return $this->success($request, $transferredStudents, 'Transferred students retrieved');
    }
}
