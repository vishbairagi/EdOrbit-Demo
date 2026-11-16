<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ClassGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassGroupApiController extends BaseApiController
{
    /**
     * List Class Groups.
     */
    public function index(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        if (! $school || ! $session) {
            return $this->error('School or academic session context is missing.', 400);
        }

        $query = ClassGroup::withCount('classes')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        // Support all list when all=1 or per_page=-1
        if ($request->boolean('all') || $request->input('per_page') == -1) {
            $classGroups = $query->orderBy('name')->get();
            return $this->success($request, $classGroups, 'Class groups retrieved successfully');
        }

        $paginated = $query->orderBy('name')->paginate($perPage);

        return $this->paginated($request, $paginated, 'Class groups retrieved successfully');
    }

    /**
     * Create Class Group.
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
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive,Active,Inactive',
        ]);

        $status = strtolower($validated['status'] ?? 'active');

        $classGroup = ClassGroup::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $status,
        ]);

        return $this->success($request, $classGroup, 'Class group created successfully', 201);
    }

    /**
     * Get Class Group Details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $classGroup = ClassGroup::with('classes.sections')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $classGroup) {
            return $this->error('Class group not found.', 404);
        }

        return $this->success($request, $classGroup, 'Class group details retrieved');
    }

    /**
     * Update Class Group.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $classGroup = ClassGroup::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $classGroup) {
            return $this->error('Class group not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive,Active,Inactive',
        ]);

        if (isset($validated['status'])) {
            $validated['status'] = strtolower($validated['status']);
        }

        $classGroup->update($validated);

        return $this->success($request, $classGroup, 'Class group updated successfully');
    }

    /**
     * Delete Class Group.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $classGroup = ClassGroup::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $classGroup) {
            return $this->error('Class group not found.', 404);
        }

        $classGroup->delete();

        return $this->success($request, null, 'Class group deleted successfully');
    }
}
