<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintIdCardController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id');

        return $id ? School::find($id) : null;
    }

    public function index(Request $request): View
    {
        $type = $request->query('type') === 'staff' ? 'staff' : 'student';
        $school = $this->currentSchool();
        $classesQuery = $school ? SchoolClass::where('school_id', $school->id)->orderBy('name') : null;

        $user = session('auth_guard') === 'staff' ? \App\Models\Staff::find(session('auth_user_id')) : null;
        $session = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        if ($user && ! $user->hasPermission('sm-student.print-id-cards') && $session && $classesQuery) {
            $classIds = \App\Models\ClassTeacher::where('staff_id', $user->id)
                            ->where('session_id', $session)
                            ->pluck('class_id')->toArray();
            $classesQuery->whereIn('id', $classIds);
        }

        $classes = $classesQuery ? $classesQuery->get() : collect();
        $sections = collect();
        $students = collect();
        $staffMembers = collect();

        if ($type === 'staff') {
            $staffMembers = Staff::with('role')
                ->when($school, fn ($q) => $q->where('school_id', $school->id))
                ->orderBy('name')
                ->get();
        } elseif ($request->filled('class_id')) {
            $sections = Section::where('school_class_id', $request->class_id)->get();

            $query = Student::with(['schoolClass', 'section', 'school'])
                ->where('school_class_id', $request->class_id)
                ->when($school, fn ($q) => $q->where('school_id', $school->id));

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            $students = $query->orderBy('first_name')->get();
        }

        return view('student.print-id-cards.index', compact('school', 'classes', 'sections', 'students', 'staffMembers', 'type'));
    }

    /** Print a single student's ID card */
    public function print(Student $student): View
    {
        $student->load(['school', 'schoolClass', 'section']);

        return view('student.print-id-cards.print', [
            'cards' => [$this->studentCard($student)],
            'title' => 'ID Card - '.$student->full_name,
        ]);
    }

    /** Print a single staff member's ID card */
    public function printStaff(Staff $staff): View
    {
        $staff->load(['school', 'role']);

        return view('student.print-id-cards.print', [
            'cards' => [$this->staffCard($staff)],
            'title' => 'ID Card - '.$staff->name,
        ]);
    }

    /** Print multiple selected students (or staff when type=staff) */
    public function printSelected(Request $request): View
    {
        $ids = $request->query('ids', '');
        $idArr = array_filter(explode(',', $ids));

        if ($request->query('type') === 'staff') {
            $cards = Staff::with(['school', 'role'])
                ->whereIn('id', $idArr)
                ->orderBy('name')
                ->get()
                ->map(fn (Staff $s) => $this->staffCard($s))
                ->all();
        } else {
            $cards = Student::with(['school', 'schoolClass', 'section'])
                ->whereIn('id', $idArr)
                ->get()
                ->map(fn (Student $s) => $this->studentCard($s))
                ->all();
        }

        return view('student.print-id-cards.print', [
            'cards' => $cards,
            'title' => 'Print ID Cards',
        ]);
    }

    /** @return array<string, mixed> */
    private function studentCard(Student $student): array
    {
        $dateFormat = SystemSetting::getSetting('date_format', 'Y-m-d');
        $guardianName = $student->father_name ?: ($student->guardian_name ?: $student->mother_name);
        $emergency = $student->father_phone ?: ($student->guardian_phone ?: $student->mother_phone);

        return $this->baseCard($student->school, $student->academic_session_id) + [
            'label' => 'Student ID Card',
            'name' => $student->full_name,
            'photo' => $student->photo_url,
            'front' => array_filter([
                'Adm No' => $student->admission_no,
                'Class' => $student->schoolClass?->name,
                'Section' => $student->section?->name,
                'Roll No' => $student->roll_number,
                'D.O.B.' => $student->date_of_birth?->format($dateFormat),
            ], fn ($v) => $v !== null && $v !== ''),
            'back' => array_filter([
                'Father/Guardian:' => $guardianName,
                'Emergency Contact:' => $emergency,
                'Student Phone:' => $student->phone,
                'Blood Group:' => $student->blood_group,
                'Address:' => $student->address,
            ], fn ($v) => $v !== null && $v !== ''),
        ];
    }

    /** @return array<string, mixed> */
    public function staffCard(Staff $staff): array
    {
        $dateFormat = SystemSetting::getSetting('date_format', 'Y-m-d');
        $photo = null;
        if ($staff->photo) {
            $photo = filter_var($staff->photo, FILTER_VALIDATE_URL)
                ? $staff->photo
                : asset('storage/'.ltrim($staff->photo, '/'));
        }

        return $this->baseCard($staff->school, null) + [
            'label' => 'Staff ID Card',
            'name' => $staff->name,
            'photo' => $photo,
            'front' => array_filter([
                'Emp ID' => $staff->employee_id,
                'Designation' => $staff->designation,
                'Role' => $staff->role?->name,
                'Phone' => $staff->phone,
                'D.O.B.' => $staff->date_of_birth?->format($dateFormat),
            ], fn ($v) => $v !== null && $v !== ''),
            'back' => array_filter([
                'Email:' => $staff->email,
                'Phone:' => $staff->phone,
                'Joining Date:' => $staff->joining_date?->format($dateFormat),
                'Address:' => $staff->address,
            ], fn ($v) => $v !== null && $v !== ''),
        ];
    }

    /** @return array<string, mixed> */
    private function baseCard(?School $school, ?int $sessionId): array
    {
        $schoolId = $school?->id;
        $background = $this->schoolSetting($schoolId, $sessionId, 'id_card_background');
        $signature = $this->schoolSetting($schoolId, $sessionId, 'school_signature');

        return [
            'school_name' => $school?->name ?? 'School Name',
            'school_contact' => implode(' • ', array_filter([$school?->phone, $school?->email])),
            'color' => $this->themeColor(),
            'background' => $background ? asset($background) : null,
            'signature' => $signature ? asset($signature) : null,
        ];
    }

    /** Project theme colour chosen under Appearance settings. */
    private function themeColor(): string
    {
        $color = (string) SystemSetting::getSetting('theme_color', '#B47A42');

        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#B47A42';
    }

    /**
     * Resolve a school setting, preferring the card's session, then the selected
     * session, then whichever session most recently saved a value.
     */
    private function schoolSetting(?int $schoolId, ?int $sessionId, string $key): ?string
    {
        if (! $schoolId) {
            return null;
        }

        $candidates = array_unique(array_filter([
            $sessionId,
            session('selected_session_id'),
            session('selected_academic_session_id'),
        ]));

        foreach ($candidates as $candidate) {
            $value = SchoolSetting::getSetting($schoolId, $candidate, $key);
            if ($value) {
                return $value;
            }
        }

        return SchoolSetting::where('school_id', $schoolId)
            ->where('key', $key)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->latest('id')
            ->value('value');
    }
}
