<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTimetable;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    private function getSchoolAndSession(): array
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        abort_if(! $sessionId, 403, 'No academic session selected or configured.');
        $session = AcademicSession::findOrFail($sessionId);

        return [$school, $session];
    }

    /**
     * View Attendance Monthly Sheet (Grid of days 1..31, matching screenshot without radio buttons).
     */
    public function index(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::with(['sections' => fn ($q) => $q->orderBy('name')])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('class_id');
        $selectedClass = $selectedClassId ? $classes->firstWhere('id', $selectedClassId) : null;

        $sections = $selectedClass ? $selectedClass->sections : collect();
        $selectedSectionId = $request->input('section_id');

        // Month formatting: YYYY-MM (default to current month)
        $monthInput = $request->input('month', now()->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
            $monthInput = $monthCarbon->format('Y-m');
        }

        $daysInMonth = $monthCarbon->daysInMonth;
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();

        // Timetable routines for this class & section to populate the Timetable filter
        $timetableSlotsQuery = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($selectedClassId) {
            $timetableSlotsQuery->where('school_class_id', $selectedClassId);
        }
        if ($selectedSectionId && $selectedSectionId !== 'none') {
            $timetableSlotsQuery->where(function ($q) use ($selectedSectionId) {
                $q->where('section_id', $selectedSectionId)->orWhereNull('section_id');
            });
        }

        $allTimetableSlots = $timetableSlotsQuery->with(['subject', 'teacher', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $selectedSlotId = $request->input('class_timetable_id');

        // Fetch students
        $students = collect();
        if ($selectedClassId) {
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $selectedClassId);

            if ($selectedSectionId && $selectedSectionId !== 'none') {
                $studentsQuery->where('section_id', $selectedSectionId);
            } elseif ($selectedSectionId === 'none') {
                $studentsQuery->whereNull('section_id');
            }

            $students = $studentsQuery->where('status', 'Active')
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();
        }

        // Fetch attendance logs for this month
        $attendancesQuery = StudentAttendance::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        if ($selectedClassId) {
            $attendancesQuery->where('school_class_id', $selectedClassId);
        }
        if ($selectedSectionId && $selectedSectionId !== 'none') {
            $attendancesQuery->where('section_id', $selectedSectionId);
        } elseif ($selectedSectionId === 'none') {
            $attendancesQuery->whereNull('section_id');
        }
        if ($selectedSlotId) {
            $attendancesQuery->where('class_timetable_id', $selectedSlotId);
        }

        $attendanceRecords = $attendancesQuery->get();

        // Map records by [student_id][day_number] => record
        $matrix = [];
        $studentSummary = [];

        foreach ($students as $stu) {
            $matrix[$stu->id] = [];
            $studentSummary[$stu->id] = [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'holiday' => 0,
                'late' => 0,
                'half_day' => 0,
            ];
        }

        foreach ($attendanceRecords as $att) {
            $dayNum = (int) Carbon::parse($att->date)->format('j');
            $stuId = $att->student_id;

            if (! isset($matrix[$stuId])) {
                $matrix[$stuId] = [];
                $studentSummary[$stuId] = [
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'holiday' => 0,
                    'late' => 0,
                    'half_day' => 0,
                ];
            }

            // In case of multiple timetable slots, save the record or latest status
            $matrix[$stuId][$dayNum] = $att;

            $status = $att->status;
            if ($status === 'Present') {
                $studentSummary[$stuId]['present']++;
                $studentSummary[$stuId]['total']++;
            } elseif ($status === 'Absent') {
                $studentSummary[$stuId]['absent']++;
                $studentSummary[$stuId]['total']++;
            } elseif ($status === 'Holiday') {
                $studentSummary[$stuId]['holiday']++;
            } elseif ($status === 'Late') {
                $studentSummary[$stuId]['late']++;
                $studentSummary[$stuId]['total']++;
            } elseif ($status === 'Half Day') {
                $studentSummary[$stuId]['half_day']++;
                $studentSummary[$stuId]['total']++;
            }
        }

        return view('sm-academic.attendance.index', compact(
            'school',
            'session',
            'classes',
            'selectedClass',
            'selectedClassId',
            'sections',
            'selectedSectionId',
            'monthInput',
            'monthCarbon',
            'daysInMonth',
            'allTimetableSlots',
            'selectedSlotId',
            'students',
            'matrix',
            'studentSummary'
        ));
    }

    /**
     * Take Attendance Screen: Select Date + Class + Section + Timetable Slot for that day.
     */
    public function create(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::with(['sections' => fn ($q) => $q->orderBy('name')])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $date = $request->input('date', now()->format('Y-m-d'));
        try {
            $carbonDate = Carbon::parse($date);
        } catch (\Exception $e) {
            $carbonDate = now();
            $date = $carbonDate->format('Y-m-d');
        }

        $dayName = $carbonDate->format('l'); // e.g. Monday, Tuesday

        $selectedClassId = $request->input('class_id');
        $selectedClass = $selectedClassId ? $classes->firstWhere('id', $selectedClassId) : null;

        $sections = $selectedClass ? $selectedClass->sections : collect();
        $selectedSectionId = $request->input('section_id');

        // Fetch Timetable Slots for this Class & Section ON THIS DAY
        $timetableSlotsQuery = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('day', $dayName);

        if ($selectedClassId) {
            $timetableSlotsQuery->where('school_class_id', $selectedClassId);
        }
        if ($selectedSectionId && $selectedSectionId !== 'none') {
            $timetableSlotsQuery->where(function ($q) use ($selectedSectionId) {
                $q->where('section_id', $selectedSectionId)->orWhereNull('section_id');
            });
        }

        $timetableSlots = $timetableSlotsQuery->with(['subject', 'teacher', 'section'])
            ->orderBy('start_time')
            ->get();

        $selectedSlotId = $request->input('class_timetable_id', 'all');
        $selectedSlot = ($selectedSlotId !== 'all') ? $timetableSlots->firstWhere('id', $selectedSlotId) : null;

        // Fetch active students for this class & section
        $students = collect();
        if ($selectedClassId) {
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $selectedClassId);

            if ($selectedSectionId && $selectedSectionId !== 'none') {
                $studentsQuery->where('section_id', $selectedSectionId);
            } elseif ($selectedSectionId === 'none') {
                $studentsQuery->whereNull('section_id');
            }

            $students = $studentsQuery->where('status', 'Active')
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();
        }

        // Check if attendance already exists for this date, class, section, slot
        $existingRecords = StudentAttendance::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('date', $date)
            ->where('school_class_id', $selectedClassId)
            ->when($selectedSectionId && $selectedSectionId !== 'none', fn ($q) => $q->where('section_id', $selectedSectionId))
            ->when($selectedSlotId && $selectedSlotId !== 'all', fn ($q) => $q->where('class_timetable_id', $selectedSlotId))
            ->get()
            ->keyBy('student_id');

        $alreadyMarked = $existingRecords->isNotEmpty();

        return view('sm-academic.attendance.take', compact(
            'school',
            'session',
            'classes',
            'selectedClass',
            'selectedClassId',
            'sections',
            'selectedSectionId',
            'date',
            'dayName',
            'timetableSlots',
            'selectedSlotId',
            'selectedSlot',
            'students',
            'existingRecords',
            'alreadyMarked'
        ));
    }

    /**
     * Store Attendance (Bulk for all students in the selected class/section/slot or ALL slots of the day).
     */
    public function store(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'date' => 'required|date',
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable',
            'class_timetable_id' => 'nullable',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:Present,Absent,Late,Half Day,Holiday',
            'attendances.*.note' => 'nullable|string|max:255',
        ]);

        $isAllSlots = ($request->input('class_timetable_id') === 'all');
        $slot = null;
        $daySlots = collect();

        $carbonDate = Carbon::parse($validated['date']);
        $dayName = $carbonDate->format('l');

        if ($isAllSlots) {
            $daySlotsQuery = ClassTimetable::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $validated['school_class_id'])
                ->where('day', $dayName);

            if (! empty($validated['section_id']) && $validated['section_id'] !== 'none') {
                $daySlotsQuery->where(function ($q) use ($validated) {
                    $q->where('section_id', $validated['section_id'])->orWhereNull('section_id');
                });
            }

            $daySlots = $daySlotsQuery->get();
        } elseif (! empty($validated['class_timetable_id'])) {
            $slot = ClassTimetable::find($validated['class_timetable_id']);
        }

        $sectionId = (! empty($validated['section_id']) && $validated['section_id'] !== 'none')
            ? (int) $validated['section_id']
            : ($slot?->section_id ?? null);

        DB::transaction(function () use ($school, $session, $validated, $isAllSlots, $daySlots, $slot, $sectionId) {
            if ($isAllSlots && $daySlots->isNotEmpty()) {
                // Bulk mark attendance across ALL timetable slots for this day
                foreach ($daySlots as $s) {
                    foreach ($validated['attendances'] as $item) {
                        StudentAttendance::updateOrCreate(
                            [
                                'school_id' => $school->id,
                                'academic_session_id' => $session->id,
                                'school_class_id' => (int) $validated['school_class_id'],
                                'section_id' => $sectionId,
                                'student_id' => (int) $item['student_id'],
                                'class_timetable_id' => $s->id,
                                'date' => $validated['date'],
                            ],
                            [
                                'subject_id' => $s->subject_id,
                                'staff_id' => $s->staff_id,
                                'status' => $item['status'],
                                'note' => $item['note'] ?? null,
                            ]
                        );
                    }
                }
            } else {
                // Mark for specific slot or general daily
                foreach ($validated['attendances'] as $item) {
                    StudentAttendance::updateOrCreate(
                        [
                            'school_id' => $school->id,
                            'academic_session_id' => $session->id,
                            'school_class_id' => (int) $validated['school_class_id'],
                            'section_id' => $sectionId,
                            'student_id' => (int) $item['student_id'],
                            'class_timetable_id' => $slot?->id,
                            'date' => $validated['date'],
                        ],
                        [
                            'subject_id' => $slot?->subject_id,
                            'staff_id' => $slot?->staff_id,
                            'status' => $item['status'],
                            'note' => $item['note'] ?? null,
                        ]
                    );
                }
            }
        });

        $slotTitle = $isAllSlots
            ? ($daySlots->isNotEmpty() ? " (All {$daySlots->count()} slots of {$dayName})" : " (Full Day)")
            : ($slot ? " ({$slot->formatted_time_range} - {$slot->subject?->name})" : '');

        return redirect()->route('academic.attendance.index', [
            'class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? '',
            'month' => Carbon::parse($validated['date'])->format('Y-m'),
            'class_timetable_id' => $slot?->id,
        ])->with('success', "Attendance on {$validated['date']}{$slotTitle} has been saved successfully!");
    }

    /**
     * Attendance Report Screen.
     */
    public function report(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::with(['sections' => fn ($q) => $q->orderBy('name')])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('class_id');
        $selectedClass = $selectedClassId ? $classes->firstWhere('id', $selectedClassId) : null;

        $sections = $selectedClass ? $selectedClass->sections : collect();
        $selectedSectionId = $request->input('section_id');

        $monthInput = $request->input('month', now()->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
            $monthInput = $monthCarbon->format('Y-m');
        }

        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();

        $students = collect();
        if ($selectedClassId) {
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $selectedClassId);

            if ($selectedSectionId && $selectedSectionId !== 'none') {
                $studentsQuery->where('section_id', $selectedSectionId);
            } elseif ($selectedSectionId === 'none') {
                $studentsQuery->whereNull('section_id');
            }

            $students = $studentsQuery->where('status', 'Active')
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();
        }

        $attendances = StudentAttendance::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->when($selectedClassId, fn ($q) => $q->where('school_class_id', $selectedClassId))
            ->when($selectedSectionId && $selectedSectionId !== 'none', fn ($q) => $q->where('section_id', $selectedSectionId))
            ->get();

        $reportData = [];
        foreach ($students as $stu) {
            $studentAtts = $attendances->where('student_id', $stu->id);
            $present = $studentAtts->where('status', 'Present')->count();
            $absent = $studentAtts->where('status', 'Absent')->count();
            $late = $studentAtts->where('status', 'Late')->count();
            $halfDay = $studentAtts->where('status', 'Half Day')->count();
            $holiday = $studentAtts->where('status', 'Holiday')->count();

            $totalWorking = $present + $absent + $late + $halfDay;
            $rate = $totalWorking > 0 ? round((($present + ($halfDay * 0.5)) / $totalWorking) * 100, 1) : 0;

            $reportData[] = [
                'student' => $stu,
                'total_working' => $totalWorking,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'half_day' => $halfDay,
                'holiday' => $holiday,
                'rate' => $rate,
            ];
        }

        return view('sm-academic.attendance.report', compact(
            'school',
            'session',
            'classes',
            'selectedClass',
            'selectedClassId',
            'sections',
            'selectedSectionId',
            'monthInput',
            'monthCarbon',
            'reportData'
        ));
    }

    /**
     * AJAX Helper to get timetable slots on a specific date for class & section.
     */
    public function getTimetableSlots(Request $request): JsonResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $date = $request->input('date', now()->format('Y-m-d'));
        try {
            $dayName = Carbon::parse($date)->format('l');
        } catch (\Exception $e) {
            $dayName = now()->format('l');
        }

        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $query = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('day', $dayName);

        if ($classId) {
            $query->where('school_class_id', $classId);
        }
        if ($sectionId && $sectionId !== 'none') {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhereNull('section_id');
            });
        }

        $slots = $query->with(['subject', 'teacher', 'section'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'time_range' => $s->formatted_time_range,
                    'subject_name' => $s->subject?->name ?? 'Subject',
                    'subject_code' => $s->subject?->code ?? '',
                    'teacher_name' => $s->teacher?->name ?? 'No Teacher',
                    'room_number' => $s->room_number ?? '',
                    'day' => $s->day,
                    'label' => "{$s->formatted_time_range} · " . ($s->subject?->name ?? 'Subject') . ($s->teacher ? " ({$s->teacher->name})" : ''),
                ];
            });

        return response()->json([
            'day' => $dayName,
            'slots' => $slots,
        ]);
    }
}
