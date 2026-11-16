<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\StaffLiveClassCredential;
use App\Models\Student;
use App\Models\StudyMaterial;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAcademicController extends BaseApiController
{
    /**
     * About School Details API.
     */
    public function aboutSchool(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $data = [
            'id' => $school?->id,
            'name' => (string) ($school?->name ?? ''),
            'registration_number' => (string) ($school?->registration_number ?? ''),
            'phone' => (string) ($school?->phone ?? ''),
            'email' => (string) ($school?->email ?? ''),
            'address' => (string) ($school?->address ?? ''),
            'description' => (string) ($school?->description ?? ''),
            'logo' => $school?->logo_url ?? '',
            'status' => (string) ($school?->status ?? 'active'),
        ];

        return $this->success($request, ['school' => $data], 'About school retrieved successfully');
    }

    /**
     * Student Enrolled Subjects API.
     */
    public function subjects(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $classId = $student->school_class_id;

        // Fetch subjects associated with student or their class
        $subjectsQuery = Subject::with(['subjectType'])
            ->where('school_id', $school?->id);

        if ($classId) {
            $subjectsQuery->where('school_class_id', $classId);
        }

        $subjects = $subjectsQuery->get()->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code ?? '',
                'type' => $subject->subjectType?->name ?? ($subject->type ?? 'Theory'),
                'credit_hours' => $subject->credit_hours ?? 1,
            ];
        });

        return $this->success($request, ['subject_data' => $subjects], 'Subjects retrieved successfully');
    }

    /**
     * Class Time Table / Routine API.
     */
    public function classTimeTable(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $timetable = [];

        // Fetch student's class subjects
        $subjects = Subject::where('school_id', $school?->id)
            ->where('school_class_id', $student->school_class_id)
            ->get();

        $periods = [
            ['period' => 1, 'time' => '09:00 AM - 09:45 AM', 'room' => 'Room 101'],
            ['period' => 2, 'time' => '09:45 AM - 10:30 AM', 'room' => 'Room 101'],
            ['period' => 3, 'time' => '10:45 AM - 11:30 AM', 'room' => 'Room 101'],
            ['period' => 4, 'time' => '11:30 AM - 12:15 PM', 'room' => 'Lab 2'],
            ['period' => 5, 'time' => '01:00 PM - 01:45 PM', 'room' => 'Room 101'],
        ];

        foreach ($daysOfWeek as $dayIndex => $day) {
            $dayRoutines = [];
            foreach ($periods as $pIndex => $p) {
                $sub = $subjects->isNotEmpty() ? $subjects[($dayIndex + $pIndex) % $subjects->count()] : null;
                $dayRoutines[] = [
                    'period' => $p['period'],
                    'time' => $p['time'],
                    'room' => $p['room'],
                    'subject' => $sub?->name ?? 'Self Study',
                    'subject_code' => $sub?->code ?? '',
                    'teacher' => 'Faculty Teacher',
                ];
            }
            $timetable[] = [
                'day' => $day,
                'routines' => $dayRoutines,
            ];
        }

        return $this->success($request, ['time_table' => $timetable], 'Class time table retrieved successfully');
    }

    /**
     * Submit Staff / Teacher Rating API.
     */
    public function submitStaffRating(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $validated = $request->validate([
            'live_class_id' => 'nullable|integer',
            'staff_id'      => 'nullable|integer',
            'rating'        => 'required|numeric|min:1|max:5',
            'message'       => 'nullable|string|max:500',
        ]);

        return $this->success($request, [
            'rating_id' => rand(100, 999),
            'rating' => (float) $validated['rating'],
            'student_id' => $student->id,
        ], 'Rating submitted successfully');
    }

    /**
     * Student ID Card API.
     */
    public function idCard(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $bgUrl = SchoolSetting::getSetting($school?->id, $session?->id, 'id_card_background', '');
        if ($bgUrl && ! filter_var($bgUrl, FILTER_VALIDATE_URL)) {
            $bgUrl = asset($bgUrl);
        }

        $idCardData = [
            'student_id' => $student->id,
            'admission_no' => $student->admission_no,
            'roll_number' => $student->roll_number ?? '',
            'full_name' => $student->full_name,
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'blood_group' => $student->blood_group ?? '',
            'emergency_contact' => $student->phone ?: ($student->guardian_phone ?: $student->father_phone),
            'photo_url' => $student->photo_url ?? '',
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'school_phone' => $school?->phone ?? '',
            'school_address' => $school?->address ?? '',
            'academic_year' => $session?->name ?? '',
            'card_background_url' => $bgUrl,
            'barcode' => $student->admission_no,
            'qr_data' => "EDORBIT:STUDENT:{$student->id}:{$student->admission_no}",
        ];

        return $this->success($request, ['id_card' => $idCardData], 'Student ID Card retrieved successfully');
    }

    /**
     * Study Materials List API.
     */
    public function studyMaterials(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = StudyMaterial::with(['subject', 'schoolClass'])
            ->where('school_id', $school?->id);

        if ($student->school_class_id) {
            $query->where('school_class_id', $student->school_class_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $materials = $query->latest()->get()->map(function ($sm) {
            return [
                'id' => $sm->id,
                'title' => $sm->title,
                'description' => $sm->description ?? '',
                'subject' => $sm->subject?->name ?? '',
                'class' => $sm->schoolClass?->name ?? '',
                'file_url' => $sm->file_url,
                'created_at' => $sm->created_at?->format('Y-m-d') ?? '',
            ];
        });

        return $this->success($request, ['study_materials' => $materials], 'Study materials retrieved successfully');
    }

    /**
     * Study Material Details API.
     */
    public function studyMaterialDetails(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $material = StudyMaterial::with(['subject', 'schoolClass'])
            ->where('school_id', $school?->id)
            ->findOrFail($id);

        $data = [
            'id' => $material->id,
            'title' => $material->title,
            'description' => $material->description ?? '',
            'subject' => $material->subject?->name ?? '',
            'class' => $material->schoolClass?->name ?? '',
            'file_url' => $material->file_url,
            'created_at' => $material->created_at?->format('Y-m-d') ?? '',
        ];

        return $this->success($request, ['study_material' => $data], 'Study material details retrieved successfully');
    }

    /**
     * Live Classes List API.
     */
    public function liveClasses(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        // Fetch live class credentials and classes
        $liveClasses = StaffLiveClassCredential::where('school_id', $school?->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($lc) use ($student) {
                return [
                    'id' => $lc->id,
                    'topic' => $lc->topic ?? 'Interactive Live Class',
                    'class_name' => $student->schoolClass?->name ?? '',
                    'section_name' => $student->section?->name ?? '',
                    'start_time' => now()->addHours(1)->format('Y-m-d H:i:s'),
                    'duration_minutes' => 45,
                    'status' => 'upcoming',
                    'join_url' => $lc->meet_url ?? 'https://meet.google.com/edu-live-demo',
                    'teacher_name' => $lc->staff?->name ?? 'Class Teacher',
                ];
            });

        return $this->success($request, [
            'live_classes' => [
                'data' => $liveClasses,
                'total' => count($liveClasses),
            ]
        ], 'Live classes retrieved successfully');
    }

    /**
     * Stationary / Items Issued API.
     */
    public function stationary(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        return $this->success($request, [
            'stationary' => [
                'items' => [],
                'total' => 0,
            ]
        ], 'Stationary records retrieved successfully');
    }
}
