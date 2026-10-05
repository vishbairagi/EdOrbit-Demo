<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeePeriod;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentCategoryController extends Controller
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

    private function currentSession(): AcademicSession
    {
        $sessionId = request('session_id') ?: session('selected_academic_session_id');
        if ($sessionId) {
            $session = AcademicSession::find($sessionId);
            if ($session) return $session;
        }
        return AcademicSession::latest('id')->firstOrFail();
    }

    public function index(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $categories = StudentCategory::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->withCount(['students' => fn (Builder $query) => $query->where('school_id', $school->id)])
            ->orderBy('name')
            ->get();
        $studentTypes = StudentType::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();
        $sessions = AcademicSession::orderByDesc('name')->get();
        $feePeriods = FeePeriod::with('academicSession')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderByDesc('academic_session_id')
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();
        $activeTab = in_array(request('tab'), ['general', 'categories', 'student-types', 'fee-periods', 'payment-methods', 'registration', 'dashboard', 'live-classes', 'card-background', 'examination', 'email-carrier', 'email-templates'], true)
            ? request('tab')
            : 'general';

        return view('sm-school.settings.index', compact(
            'school', 'session', 'categories', 'studentTypes', 'sessions', 'feePeriods', 'paymentMethods', 'activeTab'
        ));
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
                Rule::unique('student_categories', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
            ],
        ]);

        $school->studentCategories()->create([
            'name' => $validated['name'],
            'academic_session_id' => $validated['academic_session_id'],
        ]);

        return redirect()->route('sm-school.settings.index', ['session_id' => $validated['academic_session_id'], 'tab' => 'registration'])->with('success', 'Student category added successfully.');
    }

    public function update(Request $request, StudentCategory $studentCategory): RedirectResponse
    {
        $school = $this->currentSchool();
        abort_unless((int) $studentCategory->school_id === $school->id, 404);
        $session = $studentCategory->academic_session_id ? AcademicSession::find($studentCategory->academic_session_id) : $this->currentSession();
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);

        $validated = $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('student_categories', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
                    ->ignore($studentCategory->id),
            ],
        ]);
        $newName = $validated['name'];

        DB::transaction(function () use ($school, $studentCategory, $newName, $validated): void {
            Student::where('school_id', $school->id)
                ->where('category', $studentCategory->name)
                ->update(['category' => $newName]);

            $studentCategory->update([
                'name' => $newName,
                'academic_session_id' => $validated['academic_session_id']
            ]);
        });

        return redirect()->route('sm-school.settings.index', ['session_id' => $validated['academic_session_id'], 'tab' => 'registration'])->with('success', 'Student category updated successfully.');
    }

    public function destroy(StudentCategory $studentCategory): RedirectResponse
    {
        $school = $this->currentSchool();
        abort_unless((int) $studentCategory->school_id === $school->id, 404);

        $isInUse = Student::where('school_id', $school->id)
            ->where('category', $studentCategory->name)
            ->exists();

        if ($isInUse) {
            return back()->with('error', 'This category is assigned to students and cannot be deleted.');
        }

        $studentCategory->delete();

        $session_id = $studentCategory->academic_session_id ?? $this->currentSession()->id;

        return redirect()->route('sm-school.settings.index', ['session_id' => $session_id, 'tab' => 'registration'])->with('success', 'Student category deleted successfully.');
    }
}
