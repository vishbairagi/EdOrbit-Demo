<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Http\Request;

class AssignSubjectController extends Controller
{
    private function getSchoolAndSession(): array
    {
        $schoolId = session('selected_school_id');
        abort_if(!$schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        abort_if(!$sessionId, 403, 'No academic session selected or configured.');
        $session = AcademicSession::findOrFail($sessionId);

        return [$school, $session];
    }

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $activeStaff = Staff::where('school_id', $school->id)
            ->where('status', 'Active')
            ->with('role')
            ->orderBy('name')
            ->get();

        // Session-wide subjects with relationships for analytics
        $sessionSubjects = Subject::with(['schoolClass', 'subjectType', 'teachers.role'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('school_class_id')
            ->orderBy('name')
            ->get();

        $totalSubjectsCount = $sessionSubjects->count();
        $assignedSubjects = $sessionSubjects->filter(fn ($s) => $s->teachers->isNotEmpty());
        $assignedSubjectsCount = $assignedSubjects->count();
        $unassignedSubjectsCount = max(0, $totalSubjectsCount - $assignedSubjectsCount);
        $subjectAssignmentRate = $totalSubjectsCount > 0 ? round(($assignedSubjectsCount / $totalSubjectsCount) * 100) : 0;

        $singleTeacherCount = $sessionSubjects->filter(fn ($s) => $s->teachers->count() === 1)->count();
        $coTaughtCount = $sessionSubjects->filter(fn ($s) => $s->teachers->count() > 1)->count();

        // Build teacher-wise assignments map for this session
        $teacherMap = [];
        foreach ($activeStaff as $staffMember) {
            $teacherMap[$staffMember->id] = [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'employee_id' => $staffMember->employee_id,
                'designation' => $staffMember->designation ?: ($staffMember->role?->name ?? 'Faculty'),
                'phone' => $staffMember->phone,
                'email' => $staffMember->email,
                'status' => $staffMember->status,
                'subjects' => collect(),
            ];
        }

        // Populate assigned subjects for each teacher
        foreach ($sessionSubjects as $subj) {
            foreach ($subj->teachers as $teacher) {
                if (!isset($teacherMap[$teacher->id])) {
                    $teacherMap[$teacher->id] = [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'employee_id' => $teacher->employee_id,
                        'designation' => $teacher->designation ?: ($teacher->role?->name ?? 'Faculty'),
                        'phone' => $teacher->phone,
                        'email' => $teacher->email,
                        'status' => $teacher->status,
                        'subjects' => collect(),
                    ];
                }
                $teacherMap[$teacher->id]['subjects']->push([
                    'id' => $subj->id,
                    'name' => $subj->name,
                    'code' => $subj->code,
                    'type' => $subj->type,
                    'subject_type' => $subj->subjectType?->name,
                    'class_id' => $subj->school_class_id,
                    'class_name' => $subj->schoolClass?->name ?? 'Unassigned Class',
                ]);
            }
        }

        $teacherBreakdown = collect($teacherMap)->map(function ($t) {
            $count = $t['subjects']->count();
            $workloadStatus = match(true) {
                $count === 0 => 'Unassigned',
                $count <= 2 => 'Light',
                $count <= 5 => 'Balanced',
                default => 'Heavy',
            };
            $t['count'] = $count;
            $t['workload_status'] = $workloadStatus;
            return $t;
        })->sortByDesc('count')->values();

        $totalTeachersCount = count($teacherMap);
        $assignedTeachersCount = $teacherBreakdown->filter(fn ($t) => $t['count'] > 0)->count();
        $unassignedTeachersCount = max(0, $totalTeachersCount - $assignedTeachersCount);
        $teacherDeploymentRate = $totalTeachersCount > 0 ? round(($assignedTeachersCount / $totalTeachersCount) * 100) : 0;

        $totalPairings = $sessionSubjects->sum(fn ($s) => $s->teachers->count());
        $avgSubjectsPerTeacher = $assignedTeachersCount > 0 ? round($totalPairings / $assignedTeachersCount, 1) : 0;
        $avgTeachersPerSubject = $totalSubjectsCount > 0 ? round($totalPairings / $totalSubjectsCount, 1) : 0;

        // Subject breakdown for modal
        $subjectBreakdown = $sessionSubjects->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'type' => $s->type,
                'subject_type' => $s->subjectType?->name,
                'class_id' => $s->school_class_id,
                'class_name' => $s->schoolClass?->name ?? 'Unassigned',
                'teacher_count' => $s->teachers->count(),
                'status' => $s->teachers->isEmpty() ? 'unassigned' : ($s->teachers->count() === 1 ? 'single' : 'cotaught'),
                'teachers' => $s->teachers->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'employee_id' => $t->employee_id,
                    'phone' => $t->phone,
                    'designation' => $t->designation ?: ($t->role?->name ?? 'Teacher'),
                ])->values(),
            ];
        });

        // Class breakdown for modal
        $classBreakdown = $classes->map(function ($cls) use ($sessionSubjects) {
            $clsSubjects = $sessionSubjects->where('school_class_id', $cls->id)->values();
            $totalCls = $clsSubjects->count();
            $assignedCls = $clsSubjects->filter(fn ($s) => $s->teachers->isNotEmpty())->count();
            $unassignedCls = max(0, $totalCls - $assignedCls);
            $rate = $totalCls > 0 ? round(($assignedCls / $totalCls) * 100) : 0;

            return [
                'id' => $cls->id,
                'name' => $cls->name,
                'total_subjects' => $totalCls,
                'assigned_subjects' => $assignedCls,
                'unassigned_subjects' => $unassignedCls,
                'coverage_rate' => $rate,
                'subjects' => $clsSubjects,
            ];
        });

        // Workload distribution brackets
        $workloadDistribution = [
            'unassigned' => $teacherBreakdown->where('count', 0)->count(),
            'light' => $teacherBreakdown->whereBetween('count', [1, 2])->count(),
            'balanced' => $teacherBreakdown->whereBetween('count', [3, 5])->count(),
            'heavy' => $teacherBreakdown->where('count', '>=', 6)->count(),
        ];

        $analytics = [
            'totalSubjects' => $totalSubjectsCount,
            'assignedSubjects' => $assignedSubjectsCount,
            'unassignedSubjects' => $unassignedSubjectsCount,
            'subjectAssignmentRate' => $subjectAssignmentRate,
            'singleTeacherCount' => $singleTeacherCount,
            'coTaughtCount' => $coTaughtCount,
            'totalTeachers' => $totalTeachersCount,
            'assignedTeachers' => $assignedTeachersCount,
            'unassignedTeachers' => $unassignedTeachersCount,
            'teacherDeploymentRate' => $teacherDeploymentRate,
            'totalPairings' => $totalPairings,
            'avgSubjectsPerTeacher' => $avgSubjectsPerTeacher,
            'avgTeachersPerSubject' => $avgTeachersPerSubject,
            'teacherBreakdown' => $teacherBreakdown,
            'subjectBreakdown' => $subjectBreakdown,
            'classBreakdown' => $classBreakdown,
            'workloadDistribution' => $workloadDistribution,
        ];

        // Main table query with filters
        $query = Subject::with(['schoolClass', 'subjectType', 'teachers.role'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->has('teachers');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->doesntHave('teachers');
            } elseif ($request->assignment_status === 'cotaught') {
                $query->has('teachers', '>', 1);
            }
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                    ->orWhere('code', 'like', "%{$kw}%")
                    ->orWhereHas('teachers', function ($tq) use ($kw) {
                        $tq->where('name', 'like', "%{$kw}%")
                            ->orWhere('employee_id', 'like', "%{$kw}%");
                    });
            });
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $subjects = $query->orderBy('school_class_id')->orderBy('name')->paginate($perPage)->withQueryString();

        return view('sm-academic.assign-subjects.index', compact('school', 'session', 'subjects', 'classes', 'analytics', 'perPage'));
    }

    public function teachers(Subject $subject)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subject->school_id !== $school->id, 403);

        $subject->load(['schoolClass', 'teachers.role']);

        $availableTeachers = Staff::where('school_id', $school->id)
            ->where('status', 'Active')
            ->whereHas('role', function ($q) {
                // Adjust if needed, but normally all active staff could be teachers or just specific roles
                // We'll fetch all active staff, in real cases filter by teacher roles
            })
            ->get();

        return view('sm-academic.assign-subjects.teachers', compact('subject', 'availableTeachers'));
    }

    public function assign(Request $request, Subject $subject)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subject->school_id !== $school->id, 403);

        $request->validate([
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:staff,id',
        ]);

        $subject->teachers()->syncWithoutDetaching($request->staff_ids);

        return redirect()->route('academic.assign-subjects.teachers', $subject)->with('success', 'Teachers assigned successfully.');
    }

    public function remove(Subject $subject, Staff $staff)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($subject->school_id !== $school->id, 403);
        
        $subject->teachers()->detach($staff->id);

        return redirect()->route('academic.assign-subjects.teachers', $subject)->with('success', 'Teacher removed successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        [$school] = $this->getSchoolAndSession();
        
        $subjectIds = $request->input('ids', []);
        
        if (empty($subjectIds)) {
            return redirect()->route('academic.assign-subjects.index')->with('error', 'No subjects selected.');
        }

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('school_id', $school->id)
            ->get();
            
        $count = 0;
        foreach ($subjects as $subject) {
            $subject->teachers()->detach();
            $count++;
        }

        return redirect()->route('academic.assign-subjects.index')
            ->with('success', "Teachers unassigned from {$count} subject(s).");
    }

    public function export()
    {
        [$school, $session] = $this->getSchoolAndSession();

        $subjects = Subject::with(['schoolClass', 'teachers'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        $filename = "assign_subjects_export_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Subject Name', 'Class Name', 'Teacher Names'];

        $callback = function() use($subjects, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($subjects as $sub) {
                $teachers = $sub->teachers->pluck('name')->implode(', ');
                fputcsv($file, [
                    $sub->name,
                    $sub->schoolClass ? $sub->schoolClass->name : '',
                    $teachers,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $filename = "assign_subjects_import_sample.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Subject Name', 'Class Name', 'Teacher Names'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Mathematics', 'Class 1', 'John Smith, Jane Doe']);
            fputcsv($file, ['Science', 'Class 2', 'Jane Doe']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        
        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');
        fgetcsv($handle);
        
        $importedCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3 || empty($row[0]) || empty($row[1])) continue;
            
            $subjectName = trim($row[0]);
            $className = trim($row[1]);
            $teacherNames = array_map('trim', explode(',', $row[2]));

            $class = SchoolClass::where('school_id', $school->id)->where('academic_session_id', $session->id)->where('name', $className)->first();
            
            if ($class) {
                $subject = Subject::where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->where('school_class_id', $class->id)
                    ->where('name', $subjectName)
                    ->first();
                    
                if ($subject) {
                    $staffIds = [];
                    foreach ($teacherNames as $tName) {
                        if (empty($tName)) continue;
                        $staff = Staff::where('school_id', $school->id)->where('name', $tName)->where('status', 'Active')->first();
                        if ($staff) {
                            $staffIds[] = $staff->id;
                        }
                    }
                    if (!empty($staffIds)) {
                        $subject->teachers()->syncWithoutDetaching($staffIds);
                        $importedCount++;
                    }
                }
            }
        }
        fclose($handle);

        return redirect()->route('academic.assign-subjects.index')->with('success', "$importedCount subject assignments processed successfully.");
    }
}
