<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferStudentController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id');

        return $id ? School::find($id) : null;
    }

    public function index(): View
    {
        $school = $this->currentSchool();
        $students = Student::with(['schoolClass', 'section'])
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        return view('student.transfer.index', compact('school', 'students'));
    }

    /** Show transfer certificate form */
    public function showCertificate(Student $student): View
    {
        $student->load(['school', 'schoolClass', 'section', 'session']);
        $school = $this->currentSchool();

        return view('student.transfer.certificate', compact('student', 'school'));
    }

    /** Mark student as transferred and show certificate */
    public function transfer(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'transfer_reason' => 'nullable|string|max:500',
        ]);

        $student->update(['status' => 'Transferred']);

        return redirect()->route('sm-student.transfer.certificate', $student)
            ->with('success', 'Student marked as transferred.');
    }
}
