<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolClassApiController extends BaseApiController
{
    /**
     * List Classes (with Sections and Class Group).
     */
    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $query = SchoolClass::with(['classGroup', 'sections'])
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('classGroup', function ($gq) use ($search) {
                      $gq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sections', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($classGroupId = $request->input('class_group_id')) {
            $query->where('class_group_id', $classGroupId);
        }

        if ($request->boolean('all') || $request->input('per_page') == -1 || $request->is('*class-list*')) {
            $classes = $query->orderBy('order')->orderBy('name')->get();
            return $this->success($request, $classes, 'Classes retrieved successfully');
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $paginated = $query->orderBy('order')->orderBy('name')->paginate($perPage);

        return $this->paginated($request, $paginated, 'Classes retrieved successfully');
    }

    /**
     * Create Class.
     */
    public function store(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'order' => 'nullable|integer|min:0',
            'sections' => 'nullable|array', // optional initial sections array
            'sections.*' => 'string|max:100',
        ]);

        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'class_group_id' => $validated['class_group_id'] ?? null,
            'order' => $validated['order'] ?? 0,
        ]);

        // If sections were provided in create request, create them
        if (! empty($validated['sections'])) {
            foreach ($validated['sections'] as $secName) {
                if (trim($secName)) {
                    Section::create([
                        'school_class_id' => $schoolClass->id,
                        'name' => trim($secName),
                    ]);
                }
            }
        }

        $schoolClass->load(['classGroup', 'sections']);

        return $this->success($request, $schoolClass, 'Class created successfully', 201);
    }

    /**
     * Get Class Details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolClass = SchoolClass::with(['classGroup', 'sections'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $schoolClass) {
            return $this->error('Class not found.', 404);
        }

        return $this->success($request, $schoolClass, 'Class details retrieved');
    }

    /**
     * Update Class.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolClass = SchoolClass::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $schoolClass) {
            return $this->error('Class not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $schoolClass->update($validated);
        $schoolClass->load(['classGroup', 'sections']);

        return $this->success($request, $schoolClass, 'Class updated successfully');
    }

    /**
     * Delete Class.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolClass = SchoolClass::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $schoolClass) {
            return $this->error('Class not found.', 404);
        }

        // Delete associated sections first
        $schoolClass->sections()->delete();
        $schoolClass->delete();

        return $this->success($request, null, 'Class deleted successfully');
    }

    // ==========================================
    // SECTIONS CRUD
    // ==========================================

    /**
     * List Sections (optionally filtered by class_id).
     */
    public function listSections(Request $request, $classId = null): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $targetClassId = $classId ?: $request->input('class_id');

        $query = Section::with('schoolClass')
            ->whereHas('schoolClass', function ($q) use ($school, $session) {
                $q->where('school_id', $school?->id)
                  ->where('academic_session_id', $session?->id);
            });

        if ($targetClassId) {
            $query->where('school_class_id', $targetClassId);
        }

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sections = $query->orderBy('name')->get();

        return $this->success($request, $sections, 'Sections retrieved successfully');
    }

    /**
     * Create Section.
     */
    public function storeSection(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'school_class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        // Verify class belongs to this school and session
        $schoolClass = SchoolClass::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($validated['school_class_id']);

        if (! $schoolClass) {
            return $this->error('Target class does not exist in the current school and session.', 404);
        }

        $section = Section::create([
            'school_class_id' => $schoolClass->id,
            'name' => $validated['name'],
            'capacity' => $validated['capacity'] ?? null,
        ]);

        $section->load('schoolClass');

        return $this->success($request, $section, 'Section created successfully', 201);
    }

    /**
     * Show Section Details.
     */
    public function showSection(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $section = Section::with('schoolClass')
            ->whereHas('schoolClass', function ($q) use ($school, $session) {
                $q->where('school_id', $school?->id)
                  ->where('academic_session_id', $session?->id);
            })
            ->find($id);

        if (! $section) {
            return $this->error('Section not found.', 404);
        }

        return $this->success($request, $section, 'Section details retrieved');
    }

    /**
     * Update Section.
     */
    public function updateSection(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $section = Section::whereHas('schoolClass', function ($q) use ($school, $session) {
            $q->where('school_id', $school?->id)
              ->where('academic_session_id', $session?->id);
        })->find($id);

        if (! $section) {
            return $this->error('Section not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
            'school_class_id' => 'nullable|exists:classes,id',
        ]);

        $section->update($validated);
        $section->load('schoolClass');

        return $this->success($request, $section, 'Section updated successfully');
    }

    /**
     * Delete Section.
     */
    public function destroySection(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $section = Section::whereHas('schoolClass', function ($q) use ($school, $session) {
            $q->where('school_id', $school?->id)
              ->where('academic_session_id', $session?->id);
        })->find($id);

        if (! $section) {
            return $this->error('Section not found.', 404);
        }

        $section->delete();

        return $this->success($request, null, 'Section deleted successfully');
    }
}
