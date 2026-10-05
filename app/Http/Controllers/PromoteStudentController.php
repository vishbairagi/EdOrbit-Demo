<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoteStudentController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id');

        return $id ? School::find($id) : null;
    }

    public function index(): View
    {
        $school = $this->currentSchool();
        $sessions = AcademicSession::orderBy('name')->get();

        $currentSessionId = SystemSetting::getSetting('current_session_id');
        $currentSession = $currentSessionId ? AcademicSession::find($currentSessionId) : null;

        $classes = ($school && $currentSession)
            ? SchoolClass::where('school_id', $school->id)
                ->where('academic_session_id', $currentSession->id)
                ->orderBy('order')
                ->orderBy('name')
                ->get()
            : collect();

        return view('student.promote.index', compact('school', 'sessions', 'classes', 'currentSession'));
    }

    /** Get classes for a specific target session (AJAX) */
    public function getClassesBySession(AcademicSession $academicSession)
    {
        $school = $this->currentSchool();
        $classes = SchoolClass::where('school_id', $school?->id)
            ->where('academic_session_id', $academicSession->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    /** Get students for a class in a session (AJAX) */
    public function getStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $school = $this->currentSchool();
        $students = Student::with(['section'])
            ->where('school_class_id', $request->class_id)
            ->where('academic_session_id', $request->session_id)
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_no', 'roll_number', 'status', 'section_id']);

        return response()->json($students->map(fn ($s) => [
            'id' => $s->id,
            'full_name' => $s->full_name,
            'admission_no' => $s->admission_no,
            'roll_number' => $s->roll_number,
            'section' => $s->section?->name,
            'status' => $s->status,
        ]));
    }

    /** Perform promotion */
    public function promote(Request $request): RedirectResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id' => 'required|exists:school_classes,id',
        ]);

        $school = $this->currentSchool();
        $toSession = AcademicSession::findOrFail($request->to_session_id);
        $toClass = SchoolClass::findOrFail($request->to_class_id);
        $promotedCount = 0;

        foreach ($request->student_ids as $studentId) {
            $original = Student::findOrFail($studentId);

            // Check if student already has an enrollment in the target session
            $existing = Student::where('admission_no', $original->admission_no)
                ->where('academic_session_id', $toSession->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'school_class_id' => $toClass->id,
                    'section_id' => null,
                    'status' => 'Active',
                ]);
            } else {
                // Create new enrollment in the new session / class
                $newStudent = $original->replicate(['academic_session_id', 'school_class_id', 'section_id', 'status', 'roll_number']);
                $newStudent->academic_session_id = $toSession->id;
                $newStudent->school_class_id = $toClass->id;
                $newStudent->section_id = null;
                $newStudent->status = 'Active';
                $newStudent->roll_number = null;
                $newStudent->admission_no = $original->admission_no;
                $newStudent->save();
            }

            // Mark original as promoted
            $original->update(['status' => 'Promoted']);

            $promotedCount++;
        }

        return redirect()->route('sm-student.promote.index')
            ->with('success', "{$promotedCount} student(s) promoted to {$toClass->name} in session {$toSession->name}.");
    }
}
