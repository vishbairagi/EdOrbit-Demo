<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ClassTeacher;
use App\Models\Medium;
use App\Models\NoticeBoard;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcademicApiController extends BaseApiController
{
    // ==========================================
    // 1. CLASS TEACHER (academic.class-teachers)
    // ==========================================

    public function classTeachersIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = ClassTeacher::with(['staff', 'schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('session_id', $session?->id);

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($sectionId = $request->input('section_id')) {
            $query->where('section_id', $sectionId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                })->orWhereHas('schoolClass', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->latest('id')->get(), 'Class teachers retrieved');
        }

        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Class teachers retrieved');
    }

    public function classTeacherStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $record = ClassTeacher::updateOrCreate([
            'school_id' => $school->id,
            'session_id' => $session->id,
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'] ?? null,
        ], [
            'staff_id' => $validated['staff_id'],
        ]);

        $record->load(['staff', 'schoolClass', 'section']);

        return $this->success($request, $record, 'Class teacher assigned successfully', 201);
    }

    public function classTeacherShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $record = ClassTeacher::with(['staff', 'schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('session_id', $session?->id)
            ->find($id);

        if (! $record) {
            return $this->error('Class teacher record not found.', 404);
        }

        return $this->success($request, $record, 'Class teacher details retrieved');
    }

    public function classTeacherUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $record = ClassTeacher::where('school_id', $school?->id)
            ->where('session_id', $session?->id)
            ->find($id);

        if (! $record) {
            return $this->error('Class teacher record not found.', 404);
        }

        $validated = $request->validate([
            'staff_id' => 'sometimes|required|exists:staff,id',
            'class_id' => 'sometimes|required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $record->update($validated);
        $record->load(['staff', 'schoolClass', 'section']);

        return $this->success($request, $record, 'Class teacher updated successfully');
    }

    public function classTeacherDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $record = ClassTeacher::where('school_id', $school?->id)
            ->where('session_id', $session?->id)
            ->find($id);

        if (! $record) {
            return $this->error('Class teacher record not found.', 404);
        }

        $record->delete();

        return $this->success($request, null, 'Class teacher assignment removed successfully');
    }

    // ==========================================
    // 2. MEDIUMS (academic.mediums)
    // ==========================================

    public function mediumsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $query = Medium::where('school_id', $school?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $mediums = $query->orderBy('name')->get();

        return $this->success($request, $mediums, 'Mediums retrieved successfully');
    }

    public function mediumStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $medium = Medium::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->success($request, $medium, 'Medium created successfully', 201);
    }

    public function mediumShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $medium = Medium::where('school_id', $school?->id)->find($id);

        if (! $medium) {
            return $this->error('Medium not found.', 404);
        }

        return $this->success($request, $medium, 'Medium details retrieved');
    }

    public function mediumUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $medium = Medium::where('school_id', $school?->id)->find($id);

        if (! $medium) {
            return $this->error('Medium not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $medium->update($validated);

        return $this->success($request, $medium, 'Medium updated successfully');
    }

    public function mediumDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $medium = Medium::where('school_id', $school?->id)->find($id);

        if (! $medium) {
            return $this->error('Medium not found.', 404);
        }

        $medium->delete();

        return $this->success($request, null, 'Medium deleted successfully');
    }

    // ==========================================
    // 3. SUBJECTS (academic.subjects)
    // ==========================================

    public function subjectsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = Subject::with(['schoolClass', 'subjectType', 'teachers'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($typeId = $request->input('subject_type_id')) {
            $query->where('subject_type_id', $typeId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('name')->get(), 'Subjects retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->orderBy('name')->paginate($perPage), 'Subjects retrieved');
    }

    public function subjectStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'school_class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'is_active' => 'nullable|boolean',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:staff,id',
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'type' => $validated['type'] ?? 'Theory',
            'subject_type_id' => $validated['subject_type_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (! empty($validated['teacher_ids'])) {
            $subject->teachers()->sync($validated['teacher_ids']);
        }

        $subject->load(['schoolClass', 'subjectType', 'teachers']);

        return $this->success($request, $subject, 'Subject created successfully', 201);
    }

    public function subjectShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $subject = Subject::with(['schoolClass', 'subjectType', 'teachers'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $subject) {
            return $this->error('Subject not found.', 404);
        }

        return $this->success($request, $subject, 'Subject details retrieved');
    }

    public function subjectUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $subject = Subject::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $subject) {
            return $this->error('Subject not found.', 404);
        }

        $validated = $request->validate([
            'school_class_id' => 'sometimes|required|exists:classes,id',
            'name' => 'sometimes|required|string|max:150',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'subject_type_id' => 'nullable|exists:subject_types,id',
            'is_active' => 'nullable|boolean',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:staff,id',
        ]);

        $subject->update($validated);

        if (isset($validated['teacher_ids'])) {
            $subject->teachers()->sync($validated['teacher_ids']);
        }

        $subject->load(['schoolClass', 'subjectType', 'teachers']);

        return $this->success($request, $subject, 'Subject updated successfully');
    }

    public function subjectDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $subject = Subject::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $subject) {
            return $this->error('Subject not found.', 404);
        }

        $subject->teachers()->detach();
        $subject->delete();

        return $this->success($request, null, 'Subject deleted successfully');
    }

    public function assignStaffToSubject(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $subject->teachers()->syncWithoutDetaching([$request->staff_id]);

        return $this->success($request, $subject->load('teachers'), 'Staff assigned to subject successfully');
    }

    // ==========================================
    // 4. SUBJECT TYPES (academic.subject-types)
    // ==========================================

    public function subjectTypesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = SubjectType::withCount('subjects')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $types = $query->orderBy('name')->get();

        return $this->success($request, $types, 'Subject types retrieved');
    }

    public function subjectTypeStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $type = SubjectType::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->success($request, $type, 'Subject type created successfully', 201);
    }

    public function subjectTypeShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $type = SubjectType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $type) {
            return $this->error('Subject type not found.', 404);
        }

        return $this->success($request, $type, 'Subject type details retrieved');
    }

    public function subjectTypeUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $type = SubjectType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $type) {
            return $this->error('Subject type not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $type->update($validated);

        return $this->success($request, $type, 'Subject type updated successfully');
    }

    public function subjectTypeDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $type = SubjectType::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $type) {
            return $this->error('Subject type not found.', 404);
        }

        $type->delete();

        return $this->success($request, null, 'Subject type deleted successfully');
    }

    // ==========================================
    // 5. STUDY MATERIALS (academic.study-materials)
    // ==========================================

    public function studyMaterialsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = StudyMaterial::with(['schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($sectionId = $request->input('section_id')) {
            $query->where('section_id', $sectionId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('book_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Study materials retrieved');
    }

    public function studyMaterialStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'book_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:20480', // 20MB max
            'file_url' => 'nullable|string|url',
        ]);

        $filePath = $validated['file_url'] ?? null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('study_materials', 'public');
        }

        $material = StudyMaterial::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'title' => $validated['title'],
            'book_name' => $validated['book_name'] ?? null,
            'type' => $validated['type'] ?? 'General',
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'is_active' => true,
        ]);

        $material->load(['schoolClass', 'section']);

        return $this->success($request, $material, 'Study material uploaded successfully', 201);
    }

    public function studyMaterialShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $material = StudyMaterial::with(['schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $material) {
            return $this->error('Study material not found.', 404);
        }

        return $this->success($request, $material, 'Study material details retrieved');
    }

    public function studyMaterialUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $material = StudyMaterial::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $material) {
            return $this->error('Study material not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'school_class_id' => 'sometimes|required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'book_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
            'file_url' => 'nullable|string|url',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('study_materials', 'public');
        } elseif (isset($validated['file_url'])) {
            $validated['file_path'] = $validated['file_url'];
        }

        $material->update($validated);
        $material->load(['schoolClass', 'section']);

        return $this->success($request, $material, 'Study material updated successfully');
    }

    public function studyMaterialDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $material = StudyMaterial::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $material) {
            return $this->error('Study material not found.', 404);
        }

        $material->delete();

        return $this->success($request, null, 'Study material deleted successfully');
    }

    // ==========================================
    // 6. NOTICEBOARD (academic.noticeboards)
    // ==========================================

    public function noticeboardsIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = NoticeBoard::with(['schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($classId = $request->input('class_id')) {
            $query->where('school_class_id', $classId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->latest('id')->paginate($perPage), 'Noticeboard notices retrieved');
    }

    public function noticeboardStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);
        $user = $this->getUser($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'school_class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'link_type' => 'nullable|in:none,attachment,url',
            'link_url' => 'nullable|url',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachmentPath = null;
        $origName = null;
        $size = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $origName = $file->getClientOriginalName();
            $size = $file->getSize();
            $attachmentPath = $file->store('notices', 'public');
            $validated['link_type'] = 'attachment';
        }

        $notice = NoticeBoard::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $validated['school_class_id'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'link_type' => $validated['link_type'] ?? 'none',
            'link_url' => $validated['link_url'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $origName,
            'attachment_size' => $size,
            'is_active' => true,
            'status' => 'Published',
            'added_by' => $user->name ?? 'Staff',
        ]);

        $notice->load(['schoolClass', 'section']);

        return $this->success($request, $notice, 'Notice created successfully', 201);
    }

    public function noticeboardShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $notice = NoticeBoard::with(['schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $notice) {
            return $this->error('Notice not found.', 404);
        }

        return $this->success($request, $notice, 'Notice details retrieved');
    }

    public function noticeboardUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $notice = NoticeBoard::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $notice) {
            return $this->error('Notice not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'school_class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'link_type' => 'nullable|in:none,attachment,url',
            'link_url' => 'nullable|url',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $validated['attachment_original_name'] = $file->getClientOriginalName();
            $validated['attachment_size'] = $file->getSize();
            $validated['attachment_path'] = $file->store('notices', 'public');
            $validated['link_type'] = 'attachment';
        }

        $notice->update($validated);
        $notice->load(['schoolClass', 'section']);

        return $this->success($request, $notice, 'Notice updated successfully');
    }

    public function noticeboardDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $notice = NoticeBoard::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $notice) {
            return $this->error('Notice not found.', 404);
        }

        $notice->delete();

        return $this->success($request, null, 'Notice deleted successfully');
    }
}
