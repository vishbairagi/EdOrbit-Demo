<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamGroup;
use App\Models\ExamPaper;
use App\Models\ExamResult;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExaminationController extends Controller
{
    public function bulkDestroyGroup(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        ExamGroup::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', 'Selected Exam Groups deleted successfully.');
    }

    public function bulkDestroyExam(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        Exam::whereIn('id', $request->ids)->delete();
        return redirect()->back()->with('success', 'Selected Exams deleted successfully.');
    }
    public function dashboard(): View
    {
        [$school, $session] = $this->schoolContext();
        $exams = Exam::where('school_id', $school->id)->where('academic_session_id', $session->id);

        $examCount = (clone $exams)->count();
        $admitCardCount = ExamAdmitCard::whereHas('exam', fn ($query) => $query
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id))->count();
        $resultCount = ExamResult::whereHas('exam', fn ($query) => $query
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id))->count();

        return view('examination.dashboard', compact('school', 'session', 'examCount', 'admitCardCount', 'resultCount'));
    }

    public function index(): View
    {
        [$school, $session] = $this->schoolContext();
        $exams = Exam::with(['schoolClass', 'examGroup', 'sections'])
            ->withCount(['papers', 'admitCards', 'results'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->latest()
            ->paginate(15);

        return view('examination.exams.index', compact('school', 'session', 'exams'));
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function edit(Exam $exam): View
    {
        $this->assertExamScope($exam);

        return $this->formView($exam->load(['sections', 'papers', 'examGroup']));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->saveExam($request);

        return redirect()->route('examination.exams.index')->with('success', 'Exam created successfully.');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertExamScope($exam);
        $this->saveExam($request, $exam);

        return redirect()->route('examination.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function subjects(SchoolClass $schoolClass): JsonResponse
    {
        [$school, $session] = $this->schoolContext();
        abort_unless($schoolClass->school_id === $school->id && $schoolClass->academic_session_id === $session->id, 404);

        $subjects = Subject::with('subjectType')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $schoolClass->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'type' => $subject->subjectType?->name ?? ucfirst($subject->type),
            ]);

        return response()->json($subjects);
    }

    public function groups(): View
    {
        [$school, $session] = $this->schoolContext();
        $groups = ExamGroup::withCount('exams')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('name')
            ->get();
        $editingGroup = request()->integer('edit')
            ? $groups->firstWhere('id', request()->integer('edit'))
            : null;

        return view('examination.groups.index', compact('school', 'session', 'groups', 'editingGroup'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        [$school, $session] = $this->schoolContext();
        $validated = $this->validateGroup($request, $school->id, $session->id);
        $gradeCriteria = $this->validateGradeRows($this->gradeRows($validated['grade_criteria']));

        ExamGroup::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'grade_criteria' => $gradeCriteria,
            'result_thresholds' => $validated['result_thresholds'] ?? [],
        ]);

        return redirect()->route('examination.groups.index')->with('success', 'Exam group created successfully.');
    }

    public function updateGroup(Request $request, ExamGroup $examGroup): RedirectResponse
    {
        [$school, $session] = $this->schoolContext();
        abort_unless($examGroup->school_id === $school->id && $examGroup->academic_session_id === $session->id, 404);
        $validated = $this->validateGroup($request, $school->id, $session->id, $examGroup->id);
        $gradeCriteria = $this->validateGradeRows($this->gradeRows($validated['grade_criteria']));

        $examGroup->update([
            'name' => $validated['name'],
            'grade_criteria' => $gradeCriteria,
            'result_thresholds' => $validated['result_thresholds'] ?? [],
        ]);

        return redirect()->route('examination.groups.index')->with('success', 'Exam group updated successfully.');
    }

    public function destroyGroup(ExamGroup $examGroup): RedirectResponse
    {
        [$school, $session] = $this->schoolContext();
        abort_unless($examGroup->school_id === $school->id && $examGroup->academic_session_id === $session->id, 404);
        $examGroup->delete();

        return redirect()->route('examination.groups.index')->with('success', 'Exam group deleted.');
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

    private function formView(?Exam $exam = null): View
    {
        [$school, $session] = $this->schoolContext();
        $classes = SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        $subjects = Subject::with('subjectType')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('examination.exams.form', compact('school', 'session', 'classes', 'subjects', 'exam'));
    }

    private function saveExam(Request $request, ?Exam $exam = null): Exam
    {
        [$school, $session] = $this->schoolContext();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'exam_center' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id),
            ],

            'exam_type' => ['required', Rule::in(['marks', 'grade'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'section_ids' => ['required', 'array', 'min:1'],
            'section_ids.*' => ['required', 'integer', 'distinct', 'exists:sections,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $session->id)
                    ->where('school_class_id', $request->input('school_class_id'))
                    ->where('is_active', true),
            ],
            'papers' => ['required', 'array'],
            'papers.*.subject_type' => ['nullable', 'string', 'max:100'],
            'papers.*.maximum_marks' => ['nullable', 'required_if:exam_type,marks', 'numeric', 'gt:0', 'max:100000'],
            'papers.*.paper_code' => ['nullable', 'string', 'max:100'],
            'papers.*.paper_date' => ['required', 'date'],
            'papers.*.start_time' => ['required', 'date_format:H:i,H:i:s'],
            'papers.*.end_time' => ['required', 'date_format:H:i,H:i:s', 'after:papers.*.start_time'],
            'papers.*.room_number' => ['nullable', 'string', 'max:100'],
            'grade_criteria' => ['required', 'array:percentage_from,percentage_to,grade,status', 'min:1'],
            'grade_criteria.percentage_from.*' => ['required', 'numeric', 'between:0,100'],
            'grade_criteria.percentage_to.*' => ['required', 'numeric', 'between:0,100'],
            'grade_criteria.grade.*' => ['required', 'string', 'max:20'],
            'grade_criteria.status.*' => ['required', 'string', 'max:100'],
            'result_thresholds' => ['nullable', 'array:show_subject_status,subject_pass_threshold,subject_status_header,subject_pass_label,subject_fail_label,show_overall_status,overall_pass_threshold,max_failed_subjects,overall_row_header,overall_pass_label,overall_fail_label'],
            'result_thresholds.show_subject_status' => ['nullable', 'boolean'],
            'result_thresholds.subject_pass_threshold' => ['nullable', 'numeric', 'between:0,100'],
            'result_thresholds.subject_status_header' => ['nullable', 'string', 'max:100'],
            'result_thresholds.subject_pass_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.subject_fail_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.show_overall_status' => ['nullable', 'boolean'],
            'result_thresholds.overall_pass_threshold' => ['nullable', 'numeric', 'between:0,100'],
            'result_thresholds.max_failed_subjects' => ['nullable', 'integer', 'min:0'],
            'result_thresholds.overall_row_header' => ['nullable', 'string', 'max:100'],
            'result_thresholds.overall_pass_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.overall_fail_label' => ['nullable', 'string', 'max:100'],
            'settings' => ['nullable', 'array:overall_grade,admit_card,time_table,exam_result,total_marks,total_obtained_marks,subject_type,subject_code,show_ranks,rank_criteria,teacher_signature_enabled'],
            'settings.overall_grade' => ['nullable', 'boolean'],
            'settings.admit_card' => ['nullable', Rule::in(['published', 'unpublished'])],
            'settings.time_table' => ['nullable', Rule::in(['published', 'unpublished'])],
            'settings.exam_result' => ['nullable', Rule::in(['published', 'unpublished'])],
            'settings.total_marks' => ['nullable', 'boolean'],
            'settings.total_obtained_marks' => ['nullable', 'boolean'],
            'settings.subject_type' => ['nullable', 'boolean'],
            'settings.subject_code' => ['nullable', 'boolean'],
            'settings.show_ranks' => ['nullable', 'boolean'],
            'settings.rank_criteria' => ['nullable', Rule::in(['class', 'section'])],
            'settings.teacher_signature_enabled' => ['nullable', 'boolean'],
            'teacher_signature' => ['nullable', 'image', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($exam && (int) $exam->school_class_id !== (int) $validated['school_class_id']
            && ($exam->admitCards()->exists() || $exam->results()->exists())) {
            throw ValidationException::withMessages(['school_class_id' => 'Class cannot be changed after admit cards or results have been recorded.']);
        }

        foreach ($validated['papers'] as $paper) {
            if (isset($paper['paper_date']) && ($paper['paper_date'] < $validated['start_date'] || $paper['paper_date'] > $validated['end_date'])) {
                throw ValidationException::withMessages(['papers' => 'Every paper date must be within the exam start and end dates.']);
            }
        }

        $sections = Section::whereIn('id', $validated['section_ids'])
            ->where('school_class_id', $validated['school_class_id'])
            ->count();
        if ($sections !== count(array_unique($validated['section_ids']))) {
            throw ValidationException::withMessages(['section_ids' => 'Choose sections assigned to the selected class.']);
        }

        foreach ($validated['subject_ids'] as $subjectId) {
            if (! isset($validated['papers'][$subjectId])) {
                throw ValidationException::withMessages(['papers' => 'Add paper details for every selected subject.']);
            }
        }

        $group = null;
        $gradeCriteria = $this->gradeRows($validated['grade_criteria'] ?? []);
        $gradeCriteria = $this->validateGradeRows($gradeCriteria ?: ($group?->grade_criteria ?? []));
        $thresholdDefaults = [
            'show_subject_status' => false,
            'subject_pass_threshold' => 0,
            'subject_status_header' => 'Status',
            'subject_pass_label' => 'Pass',
            'subject_fail_label' => 'Fail',
            'show_overall_status' => false,
            'overall_pass_threshold' => 0,
            'max_failed_subjects' => 0,
            'overall_row_header' => 'Overall Result',
            'overall_pass_label' => 'Pass',
            'overall_fail_label' => 'Fail',
        ];
        $resultThresholds = $validated['result_thresholds'] ?? [];
        $resultThresholds = $resultThresholds === []
            ? ($group?->result_thresholds ?? $thresholdDefaults)
            : array_replace($thresholdDefaults, $resultThresholds);
        $submittedSettings = $validated['settings'] ?? [];
        $settings = [
            'overall_grade' => (bool) ($submittedSettings['overall_grade'] ?? false),
            'admit_card' => $submittedSettings['admit_card'] ?? 'unpublished',
            'time_table' => $submittedSettings['time_table'] ?? 'unpublished',
            'exam_result' => $submittedSettings['exam_result'] ?? 'unpublished',
            'total_marks' => (bool) ($submittedSettings['total_marks'] ?? false),
            'total_obtained_marks' => (bool) ($submittedSettings['total_obtained_marks'] ?? false),
            'subject_type' => (bool) ($submittedSettings['subject_type'] ?? false),
            'subject_code' => (bool) ($submittedSettings['subject_code'] ?? false),
            'show_ranks' => (bool) ($submittedSettings['show_ranks'] ?? false),
            'rank_criteria' => $submittedSettings['rank_criteria'] ?? 'class',
            'teacher_signature_enabled' => (bool) ($submittedSettings['teacher_signature_enabled'] ?? false),
        ];

        if ($request->hasFile('teacher_signature')) {
            $settings['teacher_signature_path'] = $request->file('teacher_signature')->store('exam-signatures', 'public');
        } elseif ($exam && isset($exam->settings['teacher_signature_path'])) {
            $settings['teacher_signature_path'] = $exam->settings['teacher_signature_path'];
        }

        return DB::transaction(function () use ($exam, $school, $session, $validated, $group, $gradeCriteria, $resultThresholds, $settings): Exam {
            $exam ??= new Exam;
            $exam->fill([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $validated['school_class_id'],
                'exam_group_id' => $group?->id,
                'title' => $validated['title'],
                'exam_center' => $validated['exam_center'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'exam_type' => $validated['exam_type'],
                'grade_criteria' => $gradeCriteria,
                'result_thresholds' => $resultThresholds ?: ($group?->result_thresholds ?? []),
                'settings' => $settings,
                'status' => $validated['status'],
            ])->save();

            $exam->sections()->sync($validated['section_ids']);
            $subjects = Subject::with('subjectType')->whereIn('id', $validated['subject_ids'])->get()->keyBy('id');

            foreach ($validated['subject_ids'] as $subjectId) {
                $subject = $subjects->get($subjectId);
                $paper = $validated['papers'][$subjectId];
                ExamPaper::updateOrCreate(
                    ['exam_id' => $exam->id, 'subject_id' => $subjectId],
                    [
                        'subject_name' => $subject->name,
                        'subject_type' => $paper['subject_type'] ?? $subject->subjectType?->name ?? ucfirst($subject->type),
                        'maximum_marks' => $validated['exam_type'] === 'marks' ? $paper['maximum_marks'] : null,
                        'paper_code' => $paper['paper_code'] ?? $subject->code,
                        'paper_date' => $paper['paper_date'],
                        'start_time' => $paper['start_time'],
                        'end_time' => $paper['end_time'],
                        'room_number' => $paper['room_number'] ?? null,
                    ],
                );
            }

            $exam->papers()->whereNotIn('subject_id', $validated['subject_ids'])
                ->whereDoesntHave('resultItems')
                ->delete();

            return $exam;
        });
    }

    private function validateGroup(Request $request, int $schoolId, int $sessionId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('exam_groups', 'name')
                ->where('school_id', $schoolId)
                ->where('academic_session_id', $sessionId)
                ->ignore($ignoreId)],
            'grade_criteria' => ['required', 'array:percentage_from,percentage_to,grade,status', 'min:1'],
            'grade_criteria.percentage_from.*' => ['required', 'numeric', 'between:0,100'],
            'grade_criteria.percentage_to.*' => ['required', 'numeric', 'between:0,100'],
            'grade_criteria.grade.*' => ['required', 'string', 'max:20'],
            'grade_criteria.status.*' => ['required', 'string', 'max:100'],
            'result_thresholds' => ['required', 'array:show_subject_status,subject_pass_threshold,subject_status_header,subject_pass_label,subject_fail_label,show_overall_status,overall_pass_threshold,max_failed_subjects,overall_row_header,overall_pass_label,overall_fail_label'],
            'result_thresholds.show_subject_status' => ['nullable', 'boolean'],
            'result_thresholds.subject_pass_threshold' => ['nullable', 'numeric', 'between:0,100'],
            'result_thresholds.subject_status_header' => ['nullable', 'string', 'max:100'],
            'result_thresholds.subject_pass_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.subject_fail_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.show_overall_status' => ['nullable', 'boolean'],
            'result_thresholds.overall_pass_threshold' => ['nullable', 'numeric', 'between:0,100'],
            'result_thresholds.max_failed_subjects' => ['nullable', 'integer', 'min:0'],
            'result_thresholds.overall_row_header' => ['nullable', 'string', 'max:100'],
            'result_thresholds.overall_pass_label' => ['nullable', 'string', 'max:100'],
            'result_thresholds.overall_fail_label' => ['nullable', 'string', 'max:100'],
        ]);
    }

    private function gradeRows(array $input): array
    {
        $rows = [];
        foreach ($input['percentage_from'] ?? [] as $index => $from) {
            $to = $input['percentage_to'][$index] ?? null;
            $grade = $input['grade'][$index] ?? null;
            if ($to === null || $grade === null || $grade === '') {
                continue;
            }
            $gradeStatus = trim((string) ($input['status'][$index] ?? ''));
            if (in_array(strtolower($gradeStatus), ['active', 'inactive'], true)) {
                $gradeStatus = $grade === 'F' ? 'Fail' : 'Pass';
            }
            $rows[] = [
                'percentage_from' => (float) $from,
                'percentage_to' => (float) $to,
                'grade' => $grade,
                'status' => $gradeStatus !== '' ? $gradeStatus : ($grade === 'F' ? 'Fail' : 'Pass'),
            ];
        }

        return $rows;
    }

    private function validateGradeRows(array $rows): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages(['grade_criteria' => 'Add at least one valid grade range.']);
        }

        usort($rows, fn (array $left, array $right): int => (float) $left['percentage_from'] <=> (float) $right['percentage_from']);

        $previousEnd = null;
        foreach ($rows as $row) {
            $start = (float) $row['percentage_from'];
            $end = (float) $row['percentage_to'];
            if ($start > $end || ($previousEnd !== null && $start <= $previousEnd)) {
                throw ValidationException::withMessages(['grade_criteria' => 'Grade ranges must be ordered without overlapping percentages.']);
            }
            $previousEnd = $end;
        }

        return $rows;
    }
}
