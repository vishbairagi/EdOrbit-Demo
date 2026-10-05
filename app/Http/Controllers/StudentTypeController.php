<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StudentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentTypeController extends Controller
{
    private function currentSchool(): School
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        if (session('auth_guard') === 'school_user') {
            $user = SchoolUser::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        if (session('auth_guard') === 'staff') {
            $user = Staff::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        return $school;
    }

    private function currentSession(): \App\Models\AcademicSession
    {
        $sessionId = request('session_id') ?: session('selected_academic_session_id');
        if ($sessionId) {
            $session = \App\Models\AcademicSession::find($sessionId);
            if ($session) return $session;
        }
        return \App\Models\AcademicSession::latest('id')->firstOrFail();
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('student_types', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
            ],
        ]);
        $school->studentTypes()->create([
            'name' => $validated['name'],
            'academic_session_id' => $validated['academic_session_id'],
        ]);

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $validated['academic_session_id']])
            ->with('success', 'Student type added successfully.');
    }

    public function update(Request $request, StudentType $studentType): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $studentType->academic_session_id ? \App\Models\AcademicSession::find($studentType->academic_session_id) : $this->currentSession();
        abort_unless((int) $studentType->school_id === $school->id, 404);
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);
        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('student_types', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
                    ->ignore($studentType->id),
            ],
        ]);
        $studentType->update([
            'name' => $validated['name'],
            'academic_session_id' => $validated['academic_session_id'],
        ]);

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $validated['academic_session_id']])
            ->with('success', 'Student type updated successfully.');
    }

    public function destroy(StudentType $studentType): RedirectResponse
    {
        $school = $this->currentSchool();
        $session_id = $studentType->academic_session_id ?? $this->currentSession()->id;
        abort_unless((int) $studentType->school_id === $school->id, 404);

        if ($studentType->students()->exists()) {
            return back()->with('error', 'This student type is assigned to students and cannot be deleted.');
        }

        $studentType->delete();

        return redirect()->route('sm-school.settings.index', ['tab' => 'registration', 'session_id' => $session_id])
            ->with('success', 'Student type deleted successfully.');
    }
}
