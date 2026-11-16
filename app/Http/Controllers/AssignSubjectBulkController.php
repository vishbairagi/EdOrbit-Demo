<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignSubjectBulkController extends Controller
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

        $classes = SchoolClass::with(['sections' => function ($q) {
                $q->orderBy('name');
            }])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('class_id');
        $selectedSectionId = $request->input('section_id');

        $subjects = collect();
        $students = collect();
        $selectedClass = null;
        $sections = collect();

        if ($selectedClassId) {
            $selectedClass = $classes->firstWhere('id', $selectedClassId);

            if ($selectedClass) {
                $sections = $selectedClass->sections;

                // Fetch Subjects for this class in this academic session
                $subjects = Subject::where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->where('school_class_id', $selectedClassId)
                    ->with('subjectType')
                    ->orderBy('name')
                    ->get();

                // Fetch Students in this class (& section if selected)
                $studentsQuery = Student::where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('status', 'Active');

                if ($selectedSectionId) {
                    $studentsQuery->where('section_id', $selectedSectionId);
                }

                $students = $studentsQuery->with(['section', 'activeSubjects'])
                    ->orderByRaw('CAST(roll_number AS UNSIGNED) ASC, roll_number ASC')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        return view('sm-academic.assign-subjects-bulk.index', compact(
            'school',
            'session',
            'classes',
            'sections',
            'selectedClassId',
            'selectedSectionId',
            'selectedClass',
            'subjects',
            'students'
        ));
    }

    public function assign(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
            'assignment_option' => 'required|in:append,replace',
        ], [
            'class_id.required' => 'Please select a class.',
            'student_ids.required' => 'Please select at least one student.',
            'student_ids.min' => 'Please select at least one student.',
            'subject_ids.required' => 'Please select at least one subject to assign.',
            'subject_ids.min' => 'Please select at least one subject to assign.',
            'assignment_option.required' => 'Please choose an Assignment Option (Append or Replace).',
        ]);

        $studentIds = array_map('intval', $request->input('student_ids', []));
        $subjectIds = array_map('intval', $request->input('subject_ids', []));
        $option = $request->input('assignment_option');
        $now = now();

        DB::transaction(function () use ($studentIds, $subjectIds, $option, $now) {
            if ($option === 'replace') {
                // For selected students, mark any existing subject NOT in the selected list as removed
                DB::table('student_subjects')
                    ->whereIn('student_id', $studentIds)
                    ->whereNotIn('subject_id', $subjectIds)
                    ->update([
                        'is_removed' => true,
                        'updated_at' => $now,
                    ]);
            }

            // For every selected student and every selected subject, ensure record exists and is_removed = false
            foreach ($studentIds as $studentId) {
                foreach ($subjectIds as $subjectId) {
                    $existing = DB::table('student_subjects')
                        ->where('student_id', $studentId)
                        ->where('subject_id', $subjectId)
                        ->first();

                    if ($existing) {
                        DB::table('student_subjects')
                            ->where('id', $existing->id)
                            ->update([
                                'is_removed' => false,
                                'updated_at' => $now,
                            ]);
                    } else {
                        DB::table('student_subjects')->insert([
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'is_removed' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        });

        $studentsCount = count($studentIds);
        $subjectsCount = count($subjectIds);
        $actionDesc = $option === 'replace' ? 'replaced & assigned' : 'appended & assigned';

        return redirect()->route('academic.assign-subjects-bulk.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
        ])->with('success', "Subjects assigned successfully! {$subjectsCount} subject(s) {$actionDesc} to {$studentsCount} student(s).");
    }
}
