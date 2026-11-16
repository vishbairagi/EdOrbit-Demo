<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassGroup;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmAcademicClassTeacherController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');

        $query = ClassTeacher::with(['staff', 'schoolClass', 'section'])
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('schoolClass', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('section', function ($secq) use ($search) {
                    $secq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [2, 5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $classTeachers = $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $staffs = Staff::where('school_id', $schoolId)->where('status', 'Active')->get();
        $classes = SchoolClass::with('sections')
            ->where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $session = AcademicSession::find($sessionId);
        $totalGroups = ClassGroup::where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->count();

        // ── Analytics for Top Header Circles / Cards ───────────────────
        $totalStaff = Staff::where('school_id', $schoolId)->where('status', 'Active')->count();
        $assignedStaffCount = ClassTeacher::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->distinct('staff_id')
            ->count('staff_id');
        $unassignedStaffCount = max(0, $totalStaff - $assignedStaffCount);

        $totalClassesCount = SchoolClass::where('school_id', $schoolId)
            ->where('academic_session_id', $sessionId)
            ->count();
        $assignedClassesCount = ClassTeacher::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->distinct('class_id')
            ->count('class_id');

        $totalAssignmentsCount = ClassTeacher::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->count();

        $staffCoveragePercent = $totalStaff > 0 ? (int) round(($assignedStaffCount / $totalStaff) * 100) : 0;
        $classCoveragePercent = $totalClassesCount > 0 ? (int) round(($assignedClassesCount / $totalClassesCount) * 100) : 0;

        $teacherBreakdown = ClassTeacher::with('staff')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->selectRaw('staff_id, count(*) as count')
            ->groupBy('staff_id')
            ->orderByDesc('count')
            ->get();

        return view('sm-academic.class-teachers.index', compact(
            'classTeachers', 'staffs', 'classes', 'session', 'totalGroups',
            'totalStaff', 'assignedStaffCount', 'unassignedStaffCount',
            'totalClassesCount', 'assignedClassesCount', 'totalAssignmentsCount',
            'staffCoveragePercent', 'classCoveragePercent', 'teacherBreakdown'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');

        $staffId = $request->input('staff_id');
        $request->validate([
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where('school_id', $schoolId)
                    ->where('status', 'Active'),
            ],
        ]);

        // Multi-class and multi-section support (from interactive right panel form)
        if ($request->has('class_sections')) {
            if (! is_array($request->class_sections) || empty($request->class_sections)) {
                return back()->with('error', 'Please select at least one class or section to assign.');
            }

            $createdCount = 0;
            foreach ($request->class_sections as $classId => $sectionIds) {
                $classExists = SchoolClass::where('id', $classId)
                    ->where('school_id', $schoolId)
                    ->where('academic_session_id', $sessionId)
                    ->exists();
                if (! $classExists) continue;

                if (is_array($sectionIds) && count($sectionIds) > 0) {
                    foreach ($sectionIds as $secId) {
                        $secExists = \App\Models\Section::where('id', $secId)->where('school_class_id', $classId)->exists();
                        if (! $secExists) continue;

                        ClassTeacher::updateOrCreate(
                            [
                                'school_id'  => $schoolId,
                                'session_id' => $sessionId,
                                'class_id'   => $classId,
                                'section_id' => $secId,
                            ],
                            ['staff_id' => $staffId]
                        );
                        $createdCount++;
                    }
                } else {
                    ClassTeacher::updateOrCreate(
                        [
                            'school_id'  => $schoolId,
                            'session_id' => $sessionId,
                            'class_id'   => $classId,
                            'section_id' => null,
                        ],
                        ['staff_id' => $staffId]
                    );
                    $createdCount++;
                }
            }

            if ($createdCount === 0) {
                return back()->with('error', 'Please select at least one valid class or section to assign.');
            }

            return back()->with('success', "Class Teacher assigned to {$createdCount} class/section(s).");
        }

        // Backward compatibility for single class_id & section_id
        $validated = $request->validate([
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where('school_id', $schoolId)
                    ->where('status', 'Active'),
            ],
            'class_id' => [
                'required',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_session_id', $sessionId),
            ],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')
                    ->where('school_class_id', $request->input('class_id')),
            ],
        ]);

        $assignment = ClassTeacher::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->first();

        if (! $assignment) {
            $assignment = ClassTeacher::where('school_id', $schoolId)
                ->where('session_id', $sessionId)
                ->where('class_id', $validated['class_id'])
                ->whereNull('section_id')
                ->first();
        }

        $assignment ??= new ClassTeacher([
            'school_id' => $schoolId,
            'session_id' => $sessionId,
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
        ]);

        $assignment->fill([
            'staff_id' => $validated['staff_id'],
            'section_id' => $validated['section_id'],
        ])->save();

        return back()->with('success', 'Class Teacher assigned successfully.');
    }

    public function update(Request $request, ClassTeacher $classTeacher): RedirectResponse
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');

        abort_if($classTeacher->school_id !== $schoolId, 403);

        $validated = $request->validate([
            'staff_id' => [
                'required',
                Rule::exists('staff', 'id')
                    ->where('school_id', $schoolId)
                    ->where('status', 'Active'),
            ],
            'class_id' => [
                'required',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_session_id', $sessionId),
            ],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')
                    ->where('school_class_id', $request->input('class_id')),
            ],
        ]);

        $existing = ClassTeacher::where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('id', '!=', $classTeacher->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['class_id' => 'This class and section is already assigned to a teacher.'])->withInput();
        }

        $classTeacher->update($validated);

        return back()->with('success', 'Class Teacher updated successfully.');
    }

    public function destroy(ClassTeacher $classTeacher): RedirectResponse
    {
        $classTeacher->delete();
        return back()->with('success', 'Class Teacher assignment removed.');
    }


    public function export()
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');

        $classTeachers = ClassTeacher::with(['staff', 'schoolClass', 'section'])
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->get();

        $filename = "class_teachers_export_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Staff Name', 'Class Name', 'Section Name'];

        $callback = function() use($classTeachers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($classTeachers as $ct) {
                fputcsv($file, [
                    $ct->staff ? $ct->staff->name : '',
                    $ct->schoolClass ? $ct->schoolClass->name : '',
                    $ct->section ? $ct->section->name : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $filename = "class_teachers_import_sample.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Staff Name', 'Class Name', 'Section Name'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['John Smith', 'Class 1', 'A']);
            fputcsv($file, ['Jane Doe', 'Class 2', 'B']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        
        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle);
        
        $importedCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3 || empty($row[0]) || empty($row[1])) continue;
            
            $staffName = trim($row[0]);
            $className = trim($row[1]);
            $sectionName = trim($row[2]);

            $staff = Staff::where('school_id', $schoolId)->where('name', $staffName)->where('status', 'Active')->first();
            $class = SchoolClass::where('school_id', $schoolId)->where('academic_session_id', $sessionId)->where('name', $className)->first();

            if ($staff && $class) {
                $sectionId = null;
                if (!empty($sectionName)) {
                    $section = \App\Models\Section::where('school_class_id', $class->id)->where('name', $sectionName)->first();
                    if ($section) {
                        $sectionId = $section->id;
                    }
                }

                ClassTeacher::updateOrCreate([
                    'school_id' => $schoolId,
                    'session_id' => $sessionId,
                    'class_id' => $class->id,
                    'section_id' => $sectionId,
                ], [
                    'staff_id' => $staff->id,
                ]);
                $importedCount++;
            }
        }
        fclose($handle);

        return redirect()->route('academic.class-teachers.index')->with('success', "$importedCount assignments imported successfully.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\ClassTeacher::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}