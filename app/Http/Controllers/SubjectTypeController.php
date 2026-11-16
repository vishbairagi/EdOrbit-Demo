<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubjectTypeController extends Controller
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

        // ── Base Query: strictly scoped to school and session ──
        $query = SubjectType::withCount(['subjects' => function ($q) use ($school, $session) {
            $q->where('school_id', $school->id)->where('academic_session_id', $session->id);
        }])
        ->where('school_id', $school->id)
        ->where('academic_session_id', $session->id);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $subjectTypes = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // ── Analytics for Particular Session (Whole Session Scope) ──
        $allSessionTypes = SubjectType::with(['subjects' => function ($q) use ($school, $session) {
            $q->where('school_id', $school->id)
              ->where('academic_session_id', $session->id)
              ->with('schoolClass');
        }])
        ->where('school_id', $school->id)
        ->where('academic_session_id', $session->id)
        ->get();

        $totalSessionSubjects = Subject::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->count();

        $totalTypesCount = $allSessionTypes->count();
        $activeTypesCount = $allSessionTypes->where('is_active', true)->count();
        $inactiveTypesCount = $allSessionTypes->where('is_active', false)->count();

        $totalAssignedSubjects = $allSessionTypes->sum(fn ($t) => $t->subjects->count());
        $untypedSubjectsCount = max(0, $totalSessionSubjects - $totalAssignedSubjects);
        $categorizedRate = $totalSessionSubjects > 0 ? round(($totalAssignedSubjects / $totalSessionSubjects) * 100) : 0;
        $typesWithSubjectsCount = $allSessionTypes->filter(fn ($t) => $t->subjects->count() > 0)->count();
        $typesCoveragePercent = $totalTypesCount > 0 ? round(($typesWithSubjectsCount / $totalTypesCount) * 100) : 0;

        $typesAnalysis = $allSessionTypes->map(function ($t) use ($totalSessionSubjects) {
            $subjects = $t->subjects;
            $classes = $subjects->pluck('schoolClass.name')->filter()->unique()->values()->all();

            return [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'is_active' => $t->is_active,
                'subjects_count' => $subjects->count(),
                'percentage' => $totalSessionSubjects > 0 ? round(($subjects->count() / $totalSessionSubjects) * 100, 1) : 0,
                'classes' => $classes,
                'subjects' => $subjects,
            ];
        })->sortByDesc('subjects_count')->values();

        $mostUsedType = $typesAnalysis->first();

        $analytics = [
            'totalTypes' => $totalTypesCount,
            'activeTypes' => $activeTypesCount,
            'inactiveTypes' => $inactiveTypesCount,
            'totalSessionSubjects' => $totalSessionSubjects,
            'totalAssignedSubjects' => $totalAssignedSubjects,
            'untypedSubjects' => $untypedSubjectsCount,
            'categorizedRate' => $categorizedRate,
            'typesWithSubjects' => $typesWithSubjectsCount,
            'typesCoveragePercent' => $typesCoveragePercent,
            'mostUsedType' => $mostUsedType,
            'typesAnalysis' => $typesAnalysis,
        ];

        return view('sm-academic.subject-types.index', compact('school', 'session', 'subjectTypes', 'analytics', 'perPage'));
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subject_types', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id),
            ],
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Subject type with this name already exists in session ' . $session->name . '.',
        ]);

        $validated['school_id']           = $school->id;
        $validated['academic_session_id'] = $session->id;
        $validated['is_active']           = true;
        SubjectType::create($validated);

        return redirect()->route('academic.subject-types.index')->with('success', 'Subject type created successfully for session ' . $session->name . '.');
    }

    public function update(Request $request, SubjectType $subjectType)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subjectType->school_id !== $school->id || $subjectType->academic_session_id !== $session->id, 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subject_types', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->ignore($subjectType->id),
            ],
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable',
        ], [
            'name.unique' => 'Subject type with this name already exists in session ' . $session->name . '.',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $subjectType->update($validated);

        return redirect()->route('academic.subject-types.index')->with('success', 'Subject type updated successfully.');
    }

    public function updateStatus(Request $request, SubjectType $subjectType)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subjectType->school_id !== $school->id || $subjectType->academic_session_id !== $session->id, 403);

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);
        $subjectType->update(['is_active' => $validated['is_active']]);

        return back()->with('success', 'Subject type status updated.');
    }

    public function destroy(SubjectType $subjectType)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subjectType->school_id !== $school->id || $subjectType->academic_session_id !== $session->id, 403);
        $subjectType->delete();

        return redirect()->route('academic.subject-types.index')->with('success', 'Subject type deleted.');
    }

    /** Export subject types for current session as CSV */
    public function export(): StreamedResponse
    {
        [$school, $session] = $this->getSchoolAndSession();

        $types = SubjectType::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();

        $filename = 'subject_types_' . str_replace(' ', '_', $session->name) . '_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($types) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type Name', 'Description', 'Status']);
            foreach ($types as $type) {
                fputcsv($file, [
                    $type->name,
                    $type->description ?? '',
                    $type->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Download sample CSV for subject types import */
    public function importDemo(): StreamedResponse
    {
        $filename = 'subject_types_sample.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type Name', 'Description', 'Status']);
            fputcsv($file, ['Core',          'Main compulsory subjects',  'Active']);
            fputcsv($file, ['Language',      'Language subjects',          'Active']);
            fputcsv($file, ['Practical',     'Lab and practical work',     'Active']);
            fputcsv($file, ['Co-curricular', 'Extra-curricular activities','Active']);
            fputcsv($file, ['Elective',      'Optional subjects',          'Inactive']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Import subject types from uploaded CSV (session-scoped) */
    public function import(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('csv_file')->path(), 'r');
        fgetcsv($handle); // skip header

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if (empty($name)) { $skipped++; continue; }

            $desc     = trim($row[1] ?? '') ?: null;
            $isActive = strtolower(trim($row[2] ?? 'active')) !== 'inactive';

            SubjectType::updateOrCreate(
                ['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => $name],
                ['description' => $desc, 'is_active' => $isActive]
            );
            $imported++;
        }
        fclose($handle);

        return redirect()->route('academic.subject-types.index')
            ->with('success', "{$imported} subject type(s) imported for session {$session->name}. {$skipped} skipped.");
    }

    public function bulkDestroy(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = SubjectType::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('success', "$deleted record(s) deleted successfully for session {$session->name}.");
    }
}