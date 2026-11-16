<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubjectController extends Controller
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

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = Subject::with(['schoolClass', 'subjectType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        $user = session('auth_guard') === 'staff' ? Staff::find(session('auth_user_id')) : null;
        $classTeacherClassIds = $this->classTeacherClassIds($user, $school, $session);
        $isClassTeacher = $classTeacherClassIds !== null;

        if ($isClassTeacher || ($user && ! $user->hasPermission('academic.subjects'))) {
            $query->whereIn('school_class_id', $classTeacherClassIds ?? []);
        }

        $classesQuery = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name');

        if ($isClassTeacher || ($user && ! $user->hasPermission('academic.subjects'))) {
            $classesQuery->whereIn('id', $classTeacherClassIds ?? []);
        }
        $classes = $classesQuery->get();

        $subjectTypes = SubjectType::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // ── Analytics for Particular Session (Whole Session Scope) ──
        $sessionSubjectsQuery = Subject::with(['schoolClass', 'subjectType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($isClassTeacher || ($user && ! $user->hasPermission('academic.subjects'))) {
            $sessionSubjectsQuery->whereIn('school_class_id', $classTeacherClassIds ?? []);
        }
        $sessionSubjects = $sessionSubjectsQuery->get();

        $totalSubjectsCount = $sessionSubjects->count();
        $totalClassesCount = $classes->count();
        $classesWithSubjectsCount = $classes->filter(fn ($c) => $sessionSubjects->contains('school_class_id', $c->id))->count();
        $classesWithoutSubjectsCount = max(0, $totalClassesCount - $classesWithSubjectsCount);
        $classCoveragePercent = $totalClassesCount > 0 ? round(($classesWithSubjectsCount / $totalClassesCount) * 100) : 0;

        $distinctTypesCount = $subjectTypes->count();
        $typedSubjectsCount = $sessionSubjects->whereNotNull('subject_type_id')->count();
        $untypedSubjectsCount = $sessionSubjects->whereNull('subject_type_id')->count();
        $typedPercent = $totalSubjectsCount > 0 ? round(($typedSubjectsCount / $totalSubjectsCount) * 100) : 0;

        $practicalCount = $sessionSubjects->filter(fn ($s) => $s->type === 'practical' || strtolower($s->subjectType?->name ?? '') === 'practical')->count();
        $theoryCount = max(0, $totalSubjectsCount - $practicalCount);
        $theoryPercent = $totalSubjectsCount > 0 ? round(($theoryCount / $totalSubjectsCount) * 100) : 0;
        $practicalPercent = $totalSubjectsCount > 0 ? round(($practicalCount / $totalSubjectsCount) * 100) : 0;

        $avgSubjectsPerClass = $classesWithSubjectsCount > 0 ? round($totalSubjectsCount / $classesWithSubjectsCount, 1) : 0;

        // Class-wise breakdown for modal
        $classBreakdown = $classes->map(function ($cls) use ($sessionSubjects) {
            $clsSubjects = $sessionSubjects->where('school_class_id', $cls->id)->values();
            $practical = $clsSubjects->filter(fn ($s) => $s->type === 'practical' || strtolower($s->subjectType?->name ?? '') === 'practical')->count();
            $theory = max(0, $clsSubjects->count() - $practical);
            $types = $clsSubjects->pluck('subjectType.name')->filter()->unique()->values()->all();

            return [
                'id' => $cls->id,
                'name' => $cls->name,
                'count' => $clsSubjects->count(),
                'theory_count' => $theory,
                'practical_count' => $practical,
                'types' => $types,
                'subjects' => $clsSubjects,
            ];
        });

        // Subject Type breakdown for modal
        $typeBreakdown = $subjectTypes->map(function ($type) use ($sessionSubjects, $totalSubjectsCount) {
            $tSubjects = $sessionSubjects->where('subject_type_id', $type->id)->values();
            $classesUsing = $tSubjects->pluck('schoolClass.name')->filter()->unique()->values()->all();

            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'count' => $tSubjects->count(),
                'percentage' => $totalSubjectsCount > 0 ? round(($tSubjects->count() / $totalSubjectsCount) * 100, 1) : 0,
                'classes' => $classesUsing,
                'subjects' => $tSubjects,
            ];
        });

        if ($untypedSubjectsCount > 0) {
            $untypedSubs = $sessionSubjects->whereNull('subject_type_id')->values();
            $typeBreakdown->push([
                'id' => null,
                'name' => 'General / Untyped',
                'description' => 'Subjects without an assigned subject type',
                'count' => $untypedSubs->count(),
                'percentage' => $totalSubjectsCount > 0 ? round(($untypedSubs->count() / $totalSubjectsCount) * 100, 1) : 0,
                'classes' => $untypedSubs->pluck('schoolClass.name')->filter()->unique()->values()->all(),
                'subjects' => $untypedSubs,
            ]);
        }

        $analytics = [
            'totalSubjects' => $totalSubjectsCount,
            'totalClasses' => $totalClassesCount,
            'classesWithSubjects' => $classesWithSubjectsCount,
            'classesWithoutSubjects' => $classesWithoutSubjectsCount,
            'classCoveragePercent' => $classCoveragePercent,
            'distinctTypesCount' => $distinctTypesCount,
            'typedSubjectsCount' => $typedSubjectsCount,
            'untypedSubjectsCount' => $untypedSubjectsCount,
            'typedPercent' => $typedPercent,
            'theoryCount' => $theoryCount,
            'practicalCount' => $practicalCount,
            'theoryPercent' => $theoryPercent,
            'practicalPercent' => $practicalPercent,
            'avgSubjectsPerClass' => $avgSubjectsPerClass,
            'classBreakdown' => $classBreakdown,
            'typeBreakdown' => $typeBreakdown,
        ];

        // ── Main Table Filtering ──
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                    ->orWhere('code', 'like', "%{$kw}%");
            });
        }

        // For grouped view: group the matching subjects
        $filteredAllSubjects = (clone $query)->orderBy('school_class_id')->orderBy('name')->get();
        $subjectsByClass = $filteredAllSubjects->groupBy(fn ($item) => $item->schoolClass?->name ?? 'Unassigned');

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $subjects = $query->orderBy('school_class_id')->orderBy('name')->paginate($perPage)->withQueryString();

        return view('sm-academic.subjects.index', compact(
            'school', 'session', 'subjects', 'classes', 'subjectTypes',
            'subjectsByClass', 'isClassTeacher', 'analytics', 'perPage'
        ));
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        $user = session('auth_guard') === 'staff' ? Staff::find(session('auth_user_id')) : null;
        $classTeacherClassIds = $this->classTeacherClassIds($user, $school, $session);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id),
            ],
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'type' => 'nullable|in:theory,practical',
        ]);

        if ($classTeacherClassIds !== null && ! in_array((int) $validated['school_class_id'], $classTeacherClassIds, true)) {
            abort(403, 'You can only add subjects to your assigned class.');
        }

        $validated['school_id'] = $school->id;
        $validated['academic_session_id'] = $session->id;
        $validated['type'] = $validated['type'] ?? 'theory';

        Subject::create($validated);

        return redirect()->route('academic.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, Subject $subject)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subject->school_id !== $school->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'type' => 'nullable|in:theory,practical',
        ]);

        $subject->update($validated);

        return redirect()->route('academic.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        [$school] = $this->getSchoolAndSession();
        abort_if($subject->school_id !== $school->id, 403);
        $subject->delete();

        return redirect()->route('academic.subjects.index')->with('success', 'Subject deleted.');
    }

    private function classTeacherClassIds(?Staff $staff, School $school, AcademicSession $session): ?array
    {
        if (! $staff) {
            return null;
        }

        $classIds = ClassTeacher::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->where('staff_id', $staff->id)
            ->distinct()
            ->pluck('class_id')
            ->map(fn ($classId): int => (int) $classId)
            ->all();

        return $classIds === [] ? null : $classIds;
    }

    /** Export all subjects as CSV */
    public function export(): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();
        $subjects = Subject::with(['schoolClass', 'subjectType'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('school_class_id')
            ->orderBy('name')
            ->get();

        $filename = 'subjects_export_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['Subject Name', 'Class Name', 'Subject Code', 'Subject Type'];

        $callback = function () use ($subjects, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($subjects as $subject) {
                fputcsv($file, [
                    $subject->name,
                    $subject->schoolClass?->name ?? '',
                    $subject->code ?? '',
                    $subject->subjectType?->name ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV for subjects import */
    public function importDemo(): StreamedResponse
    {
        $filename = 'subjects_import_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['Subject Name', 'Class Name', 'Subject Code', 'Subject Type'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Mathematics',  'Class 1', 'MATH', 'Core']);
            fputcsv($file, ['Science',       'Class 1', 'SCI',  'Core']);
            fputcsv($file, ['English',       'Class 2', 'ENG',  'Language']);
            fputcsv($file, ['Urdu',          'Class 2', 'URD',  'Language']);
            fputcsv($file, ['Islamiyat',     'Class 3', 'ISL',  '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import subjects from uploaded CSV */
    public function import(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:5120',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');

        // Skip header row
        fgetcsv($handle);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            // 0-SubjectName, 1-ClassName, 2-Code(opt), 3-SubjectType(opt)
            $subjectName = trim($row[0] ?? '');
            $className   = trim($row[1] ?? '');

            if (empty($subjectName) || empty($className)) {
                $skipped++;
                continue;
            }

            // Resolve class
            $class = SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('name', $className)
                ->first();

            if (! $class) {
                $errors[] = "Row skipped ('{$subjectName}'): class '{$className}' not found.";
                $skipped++;
                continue;
            }

            // Resolve subject type (optional)
            $subjectTypeId = null;
            $typeName = trim($row[3] ?? '');
            if (! empty($typeName)) {
                $type = SubjectType::where('school_id', $school->id)
                    ->where('name', $typeName)
                    ->first();
                $subjectTypeId = $type?->id;
            }

            Subject::updateOrCreate(
                [
                    'school_id'           => $school->id,
                    'academic_session_id' => $session->id,
                    'school_class_id'     => $class->id,
                    'name'                => $subjectName,
                ],
                [
                    'code'            => trim($row[2] ?? '') ?: null,
                    'subject_type_id' => $subjectTypeId,
                    'type'            => 'theory',
                ]
            );
            $imported++;
        }
        fclose($handle);

        $message = "{$imported} subject(s) imported successfully.";
        if ($skipped) {
            $message .= " {$skipped} row(s) skipped.";
        }

        return redirect()->route('academic.subjects.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\Subject::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}