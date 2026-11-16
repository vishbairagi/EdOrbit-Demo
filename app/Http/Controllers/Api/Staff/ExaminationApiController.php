<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamGroup;
use App\Models\ExamResult;
use App\Models\ExamResultItem;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationApiController extends BaseApiController
{
    // ==========================================
    // 1. MANAGE EXAMS (examination.exams)
    // ==========================================

    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = Exam::with(['schoolClass', 'examGroup'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($groupId = $request->input('exam_group_id')) {
            $query->where('exam_group_id', $groupId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->latest('id')->get(), 'Exams retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Exams retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:classes,id',
            'exam_group_id' => 'nullable|exists:exam_groups,id',
            'exam_center' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exam_type' => 'nullable|string|max:50',
            'grade_criteria' => 'nullable|array',
            'admit_roll_prefix' => 'nullable|string|max:50',
            'admit_roll_start' => 'nullable|integer|min:1',
            'status' => 'nullable|string|max:50',
        ]);

        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'exam_group_id' => $validated['exam_group_id'] ?? null,
            'title' => $validated['title'],
            'exam_center' => $validated['exam_center'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'exam_type' => $validated['exam_type'] ?? 'General',
            'grade_criteria' => $validated['grade_criteria'] ?? null,
            'admit_roll_prefix' => $validated['admit_roll_prefix'] ?? null,
            'admit_roll_start' => $validated['admit_roll_start'] ?? 1,
            'status' => $validated['status'] ?? 'Active',
        ]);

        $exam->load(['schoolClass', 'examGroup']);

        return $this->success($request, $exam, 'Exam created successfully', 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $exam = Exam::with(['schoolClass', 'examGroup'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $exam) {
            return $this->error('Exam not found.', 404);
        }

        return $this->success($request, $exam, 'Exam details retrieved');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $exam = Exam::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $exam) {
            return $this->error('Exam not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'school_class_id' => 'sometimes|required|exists:classes,id',
            'exam_group_id' => 'nullable|exists:exam_groups,id',
            'exam_center' => 'nullable|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'exam_type' => 'nullable|string|max:50',
            'grade_criteria' => 'nullable|array',
            'admit_roll_prefix' => 'nullable|string|max:50',
            'admit_roll_start' => 'nullable|integer|min:1',
            'status' => 'nullable|string|max:50',
        ]);

        $exam->update($validated);
        $exam->load(['schoolClass', 'examGroup']);

        return $this->success($request, $exam, 'Exam updated successfully');
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $exam = Exam::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $exam) {
            return $this->error('Exam not found.', 404);
        }

        $exam->delete();

        return $this->success($request, null, 'Exam deleted successfully');
    }

    // ==========================================
    // 2. EXAM GROUPS (examination.groups)
    // ==========================================

    public function groupsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = ExamGroup::withCount('exams')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $groups = $query->orderBy('name')->get();

        return $this->success($request, $groups, 'Exam groups retrieved');
    }

    public function groupStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $group = ExamGroup::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return $this->success($request, $group, 'Exam group created successfully', 201);
    }

    public function groupShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $group = ExamGroup::with('exams')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $group) {
            return $this->error('Exam group not found.', 404);
        }

        return $this->success($request, $group, 'Exam group details retrieved');
    }

    public function groupUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $group = ExamGroup::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $group) {
            return $this->error('Exam group not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $group->update($validated);

        return $this->success($request, $group, 'Exam group updated successfully');
    }

    public function groupDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $group = ExamGroup::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $group) {
            return $this->error('Exam group not found.', 404);
        }

        $group->delete();

        return $this->success($request, null, 'Exam group deleted successfully');
    }

    // ==========================================
    // 3. ADMIT CARDS (examination.admit-cards)
    // ==========================================

    public function admitCardsIndex(Request $request, $examId = null): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $targetExamId = $examId ?: $request->input('exam_id');

        $query = ExamAdmitCard::with(['student.schoolClass', 'student.section', 'exam'])
            ->whereHas('exam', function ($q) use ($school, $session) {
                $q->where('school_id', $school?->id)->where('academic_session_id', $session?->id);
            });

        if ($targetExamId) {
            $query->where('exam_id', $targetExamId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('exam_roll_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Admit cards retrieved');
    }

    public function generateAdmitCards(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'prefix' => 'nullable|string|max:50',
            'start_number' => 'nullable|integer|min:1',
        ]);

        $exam = Exam::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->findOrFail($validated['exam_id']);

        $prefix = $validated['prefix'] ?? ($exam->admit_roll_prefix ?? 'EXAM-');
        $startNum = $validated['start_number'] ?? ($exam->admit_roll_start ?? 1);

        $students = Student::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $exam->school_class_id)
            ->where('status', 'Active')
            ->orderBy('admission_number')
            ->get();

        $generatedCount = 0;
        foreach ($students as $student) {
            $rollNo = $prefix . str_pad($startNum++, 4, '0', STR_PAD_LEFT);
            ExamAdmitCard::updateOrCreate([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ], [
                'exam_roll_number' => $rollNo,
            ]);
            $generatedCount++;
        }

        return $this->success($request, [
            'exam_id' => $exam->id,
            'generated_count' => $generatedCount,
        ], "Generated {$generatedCount} admit cards successfully");
    }

    public function admitCardShow(Request $request, $id): JsonResponse
    {
        $admitCard = ExamAdmitCard::with(['student.schoolClass', 'student.section', 'exam.schoolClass'])->find($id);

        if (! $admitCard) {
            return $this->error('Admit card not found.', 404);
        }

        return $this->success($request, $admitCard, 'Admit card details retrieved');
    }

    public function admitCardUpdate(Request $request, $id): JsonResponse
    {
        $admitCard = ExamAdmitCard::find($id);

        if (! $admitCard) {
            return $this->error('Admit card not found.', 404);
        }

        $validated = $request->validate([
            'exam_roll_number' => 'required|string|max:100',
        ]);

        $admitCard->update($validated);
        $admitCard->load(['student', 'exam']);

        return $this->success($request, $admitCard, 'Admit card updated successfully');
    }

    public function admitCardDestroy(Request $request, $id): JsonResponse
    {
        $admitCard = ExamAdmitCard::find($id);

        if (! $admitCard) {
            return $this->error('Admit card not found.', 404);
        }

        $admitCard->delete();

        return $this->success($request, null, 'Admit card deleted successfully');
    }

    // ==========================================
    // 4. EXAM RESULTS (examination.results)
    // ==========================================

    public function resultsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = ExamResult::with(['student.schoolClass', 'student.section', 'exam', 'items'])
            ->whereHas('exam', function ($q) use ($school, $session) {
                $q->where('school_id', $school?->id)->where('academic_session_id', $session?->id);
            });

        if ($examId = $request->input('exam_id')) {
            $query->where('exam_id', $examId);
        }

        if ($studentId = $request->input('student_id')) {
            $query->where('student_id', $studentId);
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Exam results retrieved');
    }

    public function resultStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'teacher_remark' => 'nullable|string|max:500',
            'school_remark' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.obtained_marks' => 'required|numeric|min:0',
            'items.*.maximum_marks' => 'required|numeric|min:1',
            'items.*.remark' => 'nullable|string|max:255',
        ]);

        $result = ExamResult::updateOrCreate([
            'exam_id' => $validated['exam_id'],
            'student_id' => $validated['student_id'],
        ], [
            'teacher_remark' => $validated['teacher_remark'] ?? null,
            'school_remark' => $validated['school_remark'] ?? null,
        ]);

        if (! empty($validated['items'])) {
            $result->items()->delete();
            foreach ($validated['items'] as $item) {
                ExamResultItem::create([
                    'exam_result_id' => $result->id,
                    'subject_id' => $item['subject_id'],
                    'obtained_marks' => $item['obtained_marks'],
                    'maximum_marks' => $item['maximum_marks'],
                    'remark' => $item['remark'] ?? null,
                ]);
            }
        }

        $result->load(['student', 'exam', 'items']);

        return $this->success($request, $result, 'Exam result saved successfully', 201);
    }

    public function resultShow(Request $request, $id): JsonResponse
    {
        $result = ExamResult::with(['student.schoolClass', 'student.section', 'exam', 'items'])->find($id);

        if (! $result) {
            return $this->error('Exam result not found.', 404);
        }

        return $this->success($request, $result, 'Exam result details retrieved');
    }

    public function resultUpdate(Request $request, $id): JsonResponse
    {
        $result = ExamResult::find($id);

        if (! $result) {
            return $this->error('Exam result not found.', 404);
        }

        $validated = $request->validate([
            'teacher_remark' => 'nullable|string|max:500',
            'school_remark' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.obtained_marks' => 'required|numeric|min:0',
            'items.*.maximum_marks' => 'required|numeric|min:1',
            'items.*.remark' => 'nullable|string|max:255',
        ]);

        $result->update([
            'teacher_remark' => $validated['teacher_remark'] ?? $result->teacher_remark,
            'school_remark' => $validated['school_remark'] ?? $result->school_remark,
        ]);

        if (isset($validated['items'])) {
            $result->items()->delete();
            foreach ($validated['items'] as $item) {
                ExamResultItem::create([
                    'exam_result_id' => $result->id,
                    'subject_id' => $item['subject_id'],
                    'obtained_marks' => $item['obtained_marks'],
                    'maximum_marks' => $item['maximum_marks'],
                    'remark' => $item['remark'] ?? null,
                ]);
            }
        }

        $result->load(['student', 'exam', 'items']);

        return $this->success($request, $result, 'Exam result updated successfully');
    }

    public function resultDestroy(Request $request, $id): JsonResponse
    {
        $result = ExamResult::find($id);

        if (! $result) {
            return $this->error('Exam result not found.', 404);
        }

        $result->items()->delete();
        $result->delete();

        return $this->success($request, null, 'Exam result deleted successfully');
    }
}
