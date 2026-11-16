<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTimetable;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ClassTimetableController extends Controller
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
     * Display Class Timetable Index Table (Class, Section, Scheduled Routines, Actions).
     */
    public function index(Request $request): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        // All classes for this school & session with sections & subjects
        $classes = SchoolClass::with([
                'sections' => fn ($q) => $q->orderBy('name'),
                'subjects' => fn ($q) => $q->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->with(['teachers' => fn ($t) => $t->where('status', 'Active')->select('staff.id', 'staff.name')])
                    ->orderBy('name'),
            ])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $activeStaff = Staff::where('school_id', $school->id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Fetch all routines for this school & session to build summary stats per class & section
        $routinesQuery = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $routinesQuery->where('school_class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            if ($request->section_id === 'none') {
                $routinesQuery->whereNull('section_id');
            } else {
                $routinesQuery->where('section_id', $request->section_id);
            }
        }

        $allRoutines = $routinesQuery->get();

        // Build list of Class + Section pairs
        $rows = [];
        $filterClassId = $request->input('class_id');
        $filterSectionId = $request->input('section_id');

        foreach ($classes as $cls) {
            if ($filterClassId && $cls->id != $filterClassId) {
                continue;
            }

            if ($cls->sections->isNotEmpty()) {
                foreach ($cls->sections as $sec) {
                    if ($filterSectionId && $filterSectionId !== 'none' && $sec->id != $filterSectionId) {
                        continue;
                    }
                    if ($filterSectionId === 'none') {
                        continue;
                    }

                    $matchedRoutines = $allRoutines->where('school_class_id', $cls->id)->where('section_id', $sec->id);
                    $days = $matchedRoutines->pluck('day')->unique()->values()->all();

                    $rows[] = [
                        'class_id' => $cls->id,
                        'class_name' => $cls->name,
                        'section_id' => $sec->id,
                        'section_name' => $sec->name,
                        'routines_count' => $matchedRoutines->count(),
                        'days' => $days,
                        'subjects_count' => $cls->subjects->count(),
                    ];
                }
            } else {
                if ($filterSectionId && $filterSectionId !== 'none') {
                    continue;
                }

                $matchedRoutines = $allRoutines->where('school_class_id', $cls->id)->whereNull('section_id');
                $days = $matchedRoutines->pluck('day')->unique()->values()->all();

                $rows[] = [
                    'class_id' => $cls->id,
                    'class_name' => $cls->name,
                    'section_id' => null,
                    'section_name' => 'All Sections',
                    'routines_count' => $matchedRoutines->count(),
                    'days' => $days,
                    'subjects_count' => $cls->subjects->count(),
                ];
            }
        }

        $totalRoutinesCount = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->count();

        $classesWithRoutinesCount = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->distinct('school_class_id')
            ->count('school_class_id');

        $classDataMap = [];
        foreach ($classes as $c) {
            $classDataMap[$c->id] = [
                'sections' => $c->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
                'subjects' => $c->subjects->map(fn ($sb) => [
                    'id' => $sb->id,
                    'name' => $sb->name,
                    'code' => $sb->code,
                    'teachers' => $sb->teachers->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
                ])->values(),
            ];
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $totalRows = count($rows);
        $currentItems = array_slice($rows, ($currentPage - 1) * $perPage, $perPage);

        $paginatedRows = new LengthAwarePaginator(
            $currentItems,
            $totalRows,
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );
        $rows = $paginatedRows;

        return view('sm-academic.class-timetable.index', compact(
            'school',
            'session',
            'classes',
            'activeStaff',
            'rows',
            'totalRoutinesCount',
            'classesWithRoutinesCount',
            'classDataMap'
        ));
    }

    /**
     * Display Class Timetable Grid (Matching Image 2).
     */
    public function show(Request $request, $schoolClassId, $sectionId = null): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $schoolClass = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->findOrFail($schoolClassId);

        $section = null;
        if ($sectionId && $sectionId !== '0' && $sectionId !== 'none') {
            $section = Section::where('school_class_id', $schoolClass->id)->find($sectionId);
        }

        // Fetch routines for this class and section
        $routinesQuery = ClassTimetable::with(['subject', 'teacher'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $schoolClass->id);

        if ($section) {
            $routinesQuery->where('section_id', $section->id);
        } else {
            $routinesQuery->whereNull('section_id');
        }

        $routines = $routinesQuery->get();

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timetableGrid = [];

        foreach ($daysOfWeek as $day) {
            $dayRoutines = $routines->where('day', $day)->sortBy(function ($item) {
                return strtotime($item->start_time);
            })->values();

            $timetableGrid[$day] = $dayRoutines;
        }

        // Classes data with subjects and teachers for the modal
        $classes = SchoolClass::with([
                'sections' => fn ($q) => $q->orderBy('name'),
                'subjects' => fn ($q) => $q->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->with(['teachers' => fn ($t) => $t->where('status', 'Active')->select('staff.id', 'staff.name')])
                    ->orderBy('name'),
            ])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $activeStaff = Staff::where('school_id', $school->id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name']);
        $subjectsData = $schoolClass->subjects()
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->with(['teachers' => fn ($t) => $t->where('status', 'Active')->select('staff.id', 'staff.name')])
            ->get()
            ->map(fn ($sb) => [
                'id' => $sb->id,
                'name' => $sb->name,
                'code' => $sb->code,
                'teachers' => $sb->teachers->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
            ])->values();

        return view('sm-academic.class-timetable.show', compact(
            'school',
            'session',
            'schoolClass',
            'section',
            'timetableGrid',
            'classes',
            'activeStaff',
            'routines',
            'subjectsData'
        ));
    }

    /**
     * AJAX endpoint: Get sections and subjects (with assigned teachers) for a class.
     */
    public function classData(Request $request, SchoolClass $schoolClass): JsonResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $sections = $schoolClass->sections()->orderBy('name')->get(['id', 'name']);
        $subjects = $schoolClass->subjects()
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->with(['teachers' => fn ($t) => $t->where('status', 'Active')->select('staff.id', 'staff.name')])
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'sections' => $sections,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Store new routine slot(s) (supports multiple days selection from Image 1).
     */
    public function store(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable',
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'nullable|exists:staff,id',
            'days' => 'required|array|min:1',
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'room_number' => 'nullable|string|max:50',
        ]);

        $sectionId = ! empty($validated['section_id']) && $validated['section_id'] !== '0' && $validated['section_id'] !== 'none'
            ? $validated['section_id']
            : null;

        $createdCount = 0;
        foreach ($validated['days'] as $day) {
            ClassTimetable::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $validated['school_class_id'],
                'section_id' => $sectionId,
                'subject_id' => $validated['subject_id'],
                'staff_id' => $validated['staff_id'] ?: null,
                'day' => $day,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'room_number' => $validated['room_number'] ?: null,
            ]);
            $createdCount++;
        }

        return back()->with('success', "Routine slot(s) added successfully for {$createdCount} day(s).");
    }

    /**
     * Update an individual routine slot.
     */
    public function update(Request $request, ClassTimetable $classTimetable): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        abort_if($classTimetable->school_id !== $school->id, 403);

        $validated = $request->validate([
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'nullable|exists:staff,id',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'room_number' => 'nullable|string|max:50',
        ]);

        $classTimetable->update([
            'day' => $validated['day'],
            'subject_id' => $validated['subject_id'],
            'staff_id' => $validated['staff_id'] ?: null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room_number' => $validated['room_number'] ?: null,
        ]);

        return back()->with('success', 'Routine slot updated successfully.');
    }

    /**
     * Delete an individual routine slot.
     */
    public function destroy(ClassTimetable $classTimetable): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        abort_if($classTimetable->school_id !== $school->id, 403);

        $classTimetable->delete();

        return back()->with('success', 'Routine slot deleted successfully.');
    }

    /**
     * Delete entire class timetable for a class and section.
     */
    public function destroyClassTimetable(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable',
        ]);

        $query = ClassTimetable::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $request->school_class_id);

        if ($request->filled('section_id') && $request->section_id !== '0' && $request->section_id !== 'none') {
            $query->where('section_id', $request->section_id);
        } else {
            $query->whereNull('section_id');
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', "Class timetable deleted ({$count} routine slot(s) removed).");
    }

    /**
     * Bulk delete timetables for selected class section rows.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'selected_keys' => 'required|array',
            'selected_keys.*' => 'string',
        ]);

        $totalRoutinesDeleted = 0;
        $sectionsDeleted = 0;

        foreach ($request->selected_keys as $key) {
            // key format: "class_id:section_id"
            $parts = explode(':', $key);
            $classId = $parts[0] ?? null;
            $sectionId = $parts[1] ?? 'none';

            if (! $classId) {
                continue;
            }

            $query = ClassTimetable::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $classId);

            if ($sectionId && $sectionId !== 'none' && $sectionId !== '0') {
                $query->where('section_id', $sectionId);
            } else {
                $query->whereNull('section_id');
            }

            $count = $query->count();
            if ($count > 0) {
                $query->delete();
                $totalRoutinesDeleted += $count;
            }
            $sectionsDeleted++;
        }

        if ($totalRoutinesDeleted > 0) {
            return back()->with('success', "Bulk delete successful: {$totalRoutinesDeleted} routine slot(s) deleted across {$sectionsDeleted} class section(s).");
        }

        return back()->with('success', 'Selected class sections had no routines scheduled.');
    }

    /**
     * Dedicated Print View (matching Image 3).
     */
    public function print(Request $request, $schoolClassId, $sectionId = null): View
    {
        [$school, $session] = $this->getSchoolAndSession();

        $schoolClass = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->findOrFail($schoolClassId);

        $section = null;
        if ($sectionId && $sectionId !== '0' && $sectionId !== 'none') {
            $section = Section::where('school_class_id', $schoolClass->id)->find($sectionId);
        }

        $routinesQuery = ClassTimetable::with(['subject', 'teacher'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $schoolClass->id);

        if ($section) {
            $routinesQuery->where('section_id', $section->id);
        } else {
            $routinesQuery->whereNull('section_id');
        }

        $routines = $routinesQuery->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timetableGrid = [];

        foreach ($daysOfWeek as $day) {
            $dayRoutines = $routines->where('day', $day)->sortBy(function ($item) {
                return strtotime($item->start_time);
            })->values();

            $timetableGrid[$day] = $dayRoutines;
        }

        return view('sm-academic.class-timetable.print', compact(
            'school',
            'session',
            'schoolClass',
            'section',
            'timetableGrid'
        ));
    }
}
