<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamResult;
use App\Models\ExamResultItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExaminationWorkflowController extends Controller
{
    public function admitCards(): View
    {
        [$school, $session] = $this->schoolContext();
        $exams = Exam::with('schoolClass')
            ->withCount('admitCards')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('examination.admit-cards.index', compact('school', 'session', 'exams'));
    }

    public function generateAdmitCardsForm(Request $request, Exam $exam): View
    {
        $this->assertExamScope($exam);
        $exam->load(['schoolClass', 'sections', 'papers']);
        $exam->loadCount('admitCards');
        $existingStudentIds = $exam->admitCards()->select('student_id');
        $students = $this->eligibleStudents($exam)
            ->whereNotIn('students.id', $existingStudentIds)
            ->when($request->input('sort_by') === 'name', fn (Builder $query) => $query
                ->orderBy('first_name')->orderBy('last_name')->orderBy('id'))
            ->when($request->input('sort_by') !== 'name', fn (Builder $query) => $query
                ->orderByRaw('roll_number IS NULL')->orderBy('roll_number')->orderBy('first_name'))
            ->get();

        return view('examination.admit-cards.generate', compact('exam', 'students'));
    }

    public function viewAdmitCards(Exam $exam): View
    {
        $this->assertExamScope($exam);
        $exam->load(['schoolClass', 'school']);
        $cards = $exam->admitCards()
            ->with(['student.section', 'student.schoolClass', 'student.activeSubjects', 'student.school'])
            ->orderBy('exam_roll_number')
            ->get();
        $papers = $exam->papers()->orderBy('paper_date')->get();

        return view('examination.admit-cards.view', compact('exam', 'cards', 'papers'));
    }

    public function generateAdmitCards(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertExamScope($exam);
        $validated = $request->validate([
            'prefix' => ['nullable', 'string', 'max:30'],
            'starting_number' => ['required', 'integer', 'min:1'],
            'sort_by' => ['required', Rule::in(['roll_number', 'name'])],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', 'exists:students,id'],
        ]);

        $existingStudentIds = $exam->admitCards()->select('student_id');
        $students = $this->eligibleStudents($exam)
            ->whereNotIn('students.id', $existingStudentIds)
            ->whereIn('students.id', $validated['student_ids'])
            ->when($validated['sort_by'] === 'name', fn (Builder $query) => $query
                ->orderBy('first_name')->orderBy('last_name')->orderBy('id'))
            ->when($validated['sort_by'] === 'roll_number', fn (Builder $query) => $query
                ->orderByRaw('roll_number IS NULL')->orderBy('roll_number')->orderBy('first_name'))
            ->get();

        if ($students->count() !== count($validated['student_ids'])) {
            throw ValidationException::withMessages(['student_ids' => 'Some selected students already have a card or are not eligible for this exam.']);
        }

        $rollNumbers = $students->keys()->map(fn (int $index): string => ($validated['prefix'] ?? '').((int) $validated['starting_number'] + $index));
        $hasRollNumberConflict = ExamAdmitCard::where('exam_id', $exam->id)
            ->whereNotIn('student_id', $students->modelKeys())
            ->whereIn('exam_roll_number', $rollNumbers)
            ->exists();
        if ($hasRollNumberConflict) {
            throw ValidationException::withMessages(['starting_number' => 'These roll numbers are already assigned to other students in this exam.']);
        }

        DB::transaction(function () use ($students, $validated, $exam): void {
            foreach ($students as $index => $student) {
                ExamAdmitCard::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'exam_roll_number' => ($validated['prefix'] ?? '').((int) $validated['starting_number'] + $index),
                ]);
            }
            $exam->update([
                'admit_roll_prefix' => $validated['prefix'] ?? null,
                'admit_roll_start' => $validated['starting_number'],
            ]);
        });

        return redirect()->route('examination.admit-cards.view', $exam)
            ->with('success', 'Admit cards generated successfully.');
    }

    public function deleteAdmitCard(Exam $exam, ExamAdmitCard $admitCard): RedirectResponse
    {
        $this->assertExamScope($exam);
        abort_unless($admitCard->exam_id === $exam->id, 404);
        $admitCard->delete();

        return redirect()->route('examination.admit-cards.view', $exam)
            ->with('success', 'Admit card deleted. The student can now receive a corrected card.');
    }

    public function bulkDeleteAdmitCards(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertExamScope($exam);
        $request->validate(['ids' => 'required|array']);
        
        $cards = $exam->admitCards()->whereIn('id', $request->ids)->get();
        foreach ($cards as $card) {
            $card->delete();
        }

        return redirect()->route('examination.admit-cards.view', $exam)
            ->with('success', count($cards) . ' admit card(s) deleted successfully.');
    }

    public function updateAdmitCard(Request $request, Exam $exam, ExamAdmitCard $admitCard): RedirectResponse
    {
        $this->assertExamScope($exam);
        abort_unless($admitCard->exam_id === $exam->id, 404);
        $request->validate(['exam_roll_number' => 'required|string|max:50']);
        
        $admitCard->update(['exam_roll_number' => $request->exam_roll_number]);
        
        return redirect()->route('examination.admit-cards.view', $exam)
            ->with('success', 'Exam Roll Number updated successfully.');
    }

    public function printAdmitCards(Request $request, Exam $exam): View
    {
        $this->assertExamScope($exam);
        $exam->load('school');
        $cardsQuery = $exam->admitCards()->with([
            'student.section',
            'student.schoolClass',
            'student.activeSubjects',
            'student.school',
        ]);
        if ($request->filled('student_id')) {
            $cardsQuery->where('student_id', $request->integer('student_id'));
        }
        $cards = $cardsQuery->orderBy('exam_roll_number')->get();
        abort_if($request->filled('student_id') && $cards->isEmpty(), 404);
        $papers = $exam->papers()->orderBy('paper_date')->get();

        return view('examination.admit-cards.print', compact('exam', 'cards', 'papers'));
    }

    public function results(): View
    {
        [$school, $session] = $this->schoolContext();
        $exams = Exam::with('schoolClass')
            ->withCount('results')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('examination.results.index', compact('school', 'session', 'exams'));
    }

    public function generateResultsForm(Request $request, Exam $exam): View
    {
        $this->assertExamScope($exam);
        $exam->load(['schoolClass', 'sections', 'papers']);
        $exam->loadCount('results');
        $existingStudentIds = $exam->results()->select('student_id');
        $students = $this->eligibleStudents($exam)
            ->whereNotIn('students.id', $existingStudentIds)
            ->when($request->input('sort_by') === 'name', fn (Builder $query) => $query
                ->orderBy('first_name')->orderBy('last_name')->orderBy('id'))
            ->when($request->input('sort_by') !== 'name', fn (Builder $query) => $query
                ->orderByRaw('roll_number IS NULL')->orderBy('roll_number')->orderBy('first_name'))
            ->get();
            
        $selectedStudentId = $request->integer('student_id');
        $selectedStudent = null;
        if ($selectedStudentId) {
            $selectedStudent = $students->firstWhere('id', $selectedStudentId);
            if (!$selectedStudent) {
                $selectedStudent = $this->eligibleStudents($exam)->whereKey($selectedStudentId)->first();
            }
        }

        $papers = $selectedStudent
            ? $exam->papers->whereIn('subject_id', $selectedStudent->activeSubjects->pluck('id'))->values()
            : collect();
            
        $existingResult = null;
        if ($selectedStudent) {
            $existingResult = $exam->results()->with('items')->where('student_id', $selectedStudent->id)->first();
        }

        return view('examination.results.generate', compact('exam', 'students', 'selectedStudent', 'papers', 'existingResult'));
    }

    public function viewResults(Exam $exam): View
    {
        $this->assertExamScope($exam);
        $exam->load('schoolClass');
        $results = $exam->results()
            ->with(['student.section', 'student.schoolClass', 'items.paper'])
            ->get()
            ->sortBy(fn (ExamResult $result): string => $result->student->full_name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('examination.results.view', compact('exam', 'results'));
    }

    public function saveResults(Request $request): RedirectResponse
    {
        [$school, $session] = $this->schoolContext();
        $validated = $request->validate([
            'exam_id' => ['required', 'integer', Rule::exists('exams', 'id')->where('school_id', $school->id)->where('academic_session_id', $session->id)],
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);
        $exam = Exam::with(['sections', 'papers'])->findOrFail($validated['exam_id']);
        $student = $this->eligibleStudents($exam)
            ->whereKey($validated['student_id'])
            ->firstOrFail();
        $paperIds = $exam->papers
            ->whereIn('subject_id', $student->activeSubjects->pluck('id'))
            ->pluck('id')
            ->all();

        if ($paperIds === []) {
            throw ValidationException::withMessages(['student_id' => 'This student has no active subjects assigned to the exam.']);
        }

        $inputKey = $exam->exam_type === 'marks' ? 'marks' : 'grades';
        $allowedGrades = collect($exam->grade_criteria ?? [])->pluck('grade')->all();
        $rules = [
            'exam_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            $inputKey => ['required', 'array:'.implode(',', $paperIds)],
        ];

        foreach ($exam->papers->whereIn('id', $paperIds) as $paper) {
            $rules[$inputKey.'.'.$paper->id] = $exam->exam_type === 'marks'
                ? ['required', 'numeric', 'min:0', 'max:'.$paper->maximum_marks]
                : ['required', Rule::in($allowedGrades)];
        }

        $validated = $request->validate($rules);
        $thresholds = $exam->result_thresholds ?? [];

        DB::transaction(function () use ($exam, $student, $paperIds, $validated, $inputKey, $thresholds): void {
            $result = ExamResult::firstOrCreate(['exam_id' => $exam->id, 'student_id' => $student->id]);

            foreach ($exam->papers->whereIn('id', $paperIds) as $paper) {
                $value = $validated[$inputKey][$paper->id];
                $percentage = $exam->exam_type === 'marks'
                    ? ((float) $value / (float) $paper->maximum_marks) * 100
                    : (float) (collect($exam->grade_criteria)->firstWhere('grade', $value)['percentage_from'] ?? 0);
                $gradeRow = collect($exam->grade_criteria ?? [])->first(fn (array $row): bool => $percentage >= (float) $row['percentage_from'] && $percentage <= (float) $row['percentage_to']);
                $grade = $exam->exam_type === 'grade' ? $value : ($gradeRow['grade'] ?? null);
                $status = $gradeRow['status'] ?? null;

                if ($thresholds['show_subject_status'] ?? false) {
                    $status = $percentage >= (float) $thresholds['subject_pass_threshold']
                        ? ($thresholds['subject_pass_label'] ?? 'Pass')
                        : ($thresholds['subject_fail_label'] ?? 'Fail');
                }

                ExamResultItem::updateOrCreate(
                    ['exam_result_id' => $result->id, 'exam_paper_id' => $paper->id],
                    [
                        'obtained_marks' => $exam->exam_type === 'marks' ? $value : null,
                        'grade' => $grade,
                        'status' => $status,
                    ],
                );
            }
        });

        return redirect()->route('examination.results.view', $exam)
            ->with('success', 'Exam result saved successfully.');
    }

    public function deleteResult(Exam $exam, ExamResult $result): RedirectResponse
    {
        $this->assertExamScope($exam);
        $result->items()->delete();
        $result->delete();

        return redirect()->route('examination.results.view', $exam)
            ->with('success', 'Result deleted successfully.');
    }

    public function bulkDeleteResults(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertExamScope($exam);
        $request->validate(['ids' => 'required|array']);
        
        $results = $exam->results()->whereIn('id', $request->ids)->get();
        foreach ($results as $result) {
            $result->items()->delete();
            $result->delete();
        }

        return redirect()->route('examination.results.view', $exam)
            ->with('success', count($results) . ' result(s) deleted successfully.');
    }

    public function academicReport(Request $request): View
    {
        [$school, $session] = $this->schoolContext();
        $classes = SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')->orderBy('name')->get();
        $schoolClass = $classes->firstWhere('id', $request->integer('class_id'));
        $sections = $schoolClass?->sections()->orderBy('name')->get() ?? collect();
        $section = $sections->firstWhere('id', $request->integer('section_id'));
        $students = $section
            ? Student::where('school_id', $school->id)
                ->where('academic_session_id', $session->id)
                ->where('school_class_id', $schoolClass->id)
                ->where('section_id', $section->id)
                ->where('status', 'Active')
                ->orderBy('first_name')->orderBy('last_name')->get()
            : collect();
        $student = $students->firstWhere('id', $request->integer('student_id'));
        $results = $student
            ? ExamResult::with(['exam', 'items.paper', 'student'])
                ->where('student_id', $student->id)
                ->whereHas('exam', fn (Builder $query) => $query
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id))
                ->latest()->get()
            : collect();

        foreach ($results as $result) {
            if (! ($result->exam->settings['show_ranks'] ?? false)) {
                continue;
            }

            $rankResults = ExamResult::with(['items.paper', 'student', 'exam'])
                ->where('exam_id', $result->exam_id)
                ->whereHas('student', function (Builder $query) use ($result): void {
                    $query->where('school_class_id', $result->exam->school_class_id);
                    if (($result->exam->settings['rank_criteria'] ?? 'class') === 'section') {
                        $query->where('section_id', $result->student->section_id);
                    }
                })
                ->get()
                ->map(function (ExamResult $rankResult): array {
                    $maximumMarks = $rankResult->items->sum(fn (ExamResultItem $item): float => (float) ($item->paper?->maximum_marks ?? 0));
                    $obtainedMarks = $rankResult->items->sum(fn (ExamResultItem $item): float => (float) ($item->obtained_marks ?? 0));
                    $gradePercentages = $rankResult->items->map(fn (ExamResultItem $item): float => (float) (collect($rankResult->exam->grade_criteria ?? [])->firstWhere('grade', $item->grade)['percentage_from'] ?? 0));
                    $score = $maximumMarks > 0
                        ? ($obtainedMarks / $maximumMarks) * 100
                        : ($gradePercentages->isNotEmpty() ? $gradePercentages->avg() : 0);

                    return ['student_id' => $rankResult->student_id, 'score' => $score];
                })
                ->sortByDesc('score')
                ->values();

            $rank = $rankResults->search(fn (array $rankRow): bool => $rankRow['student_id'] === $result->student_id);
            $result->setAttribute('rank', $rank === false ? null : $rank + 1);
        }

        return view('examination.reports.academic', compact('school', 'session', 'classes', 'schoolClass', 'sections', 'section', 'students', 'student', 'results'));
    }

    private function eligibleStudents(Exam $exam): Builder
    {
        $sectionIds = $exam->sections->pluck('id');
        $subjectIds = $exam->papers->pluck('subject_id');

        return Student::with(['section', 'schoolClass', 'activeSubjects'])
            ->where('school_id', $exam->school_id)
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('school_class_id', $exam->school_class_id)
            ->where('status', 'Active')
            ->where(function (Builder $query) use ($sectionIds): void {
                $query->whereIn('section_id', $sectionIds)->orWhereNull('section_id');
            })
            ->whereHas('activeSubjects', fn (Builder $query) => $query->whereIn('subjects.id', $subjectIds));
    }

    /** @return array{School, AcademicSession} */
    private function schoolContext(): array
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);
        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        abort_if(! $sessionId, 422, 'No academic session selected.');

        return [$school, AcademicSession::findOrFail($sessionId)];
    }

    private function assertExamScope(Exam $exam): void
    {
        [$school, $session] = $this->schoolContext();
        abort_unless($exam->school_id === $school->id && $exam->academic_session_id === $session->id, 404);
    }
}
