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

        $query = Subject::with(['schoolClass', 'subjectType', 'teachers'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

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

        $subjects = $query->orderBy('school_class_id')->orderBy('name')->get();
        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')->get();

        return view('sm-academic.assign-subjects.index', compact('school', 'session', 'subjects', 'classes'));
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
