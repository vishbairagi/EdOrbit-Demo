<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseApiController extends Controller
{
    /**
     * Retrieve the resolved School model for the current request.
     */
    protected function getSchool(Request $request): ?School
    {
        $school = $request->attributes->get('school');
        if ($school instanceof School) {
            return $school;
        }

        $schoolId = $request->attributes->get('school_id');
        if (! $schoolId) {
            $user = $request->attributes->get('auth_user') ?: $request->attributes->get('student');
            $schoolId = $user?->school_id;
        }

        $school = $schoolId ? School::find($schoolId) : null;
        if (! $school) {
            $school = School::first();
        }

        return $school;
    }

    /**
     * Retrieve the resolved Academic Session for the current request.
     */
    protected function getSession(Request $request): ?AcademicSession
    {
        return $request->attributes->get('session') ?: $request->attributes->get('active_session');
    }

    /**
     * Retrieve the authenticated Student model (for student APIs).
     */
    protected function getStudent(Request $request)
    {
        return $request->attributes->get('student');
    }

    /**
     * Retrieve the authenticated user model (Staff, SchoolUser, or Student).
     */
    protected function getUser(Request $request)
    {
        return $request->attributes->get('auth_user') ?: $request->attributes->get('student');
    }

    /**
     * Retrieve the user type ('admin', 'teacher', 'super_admin', 'student').
     */
    protected function getUserType(Request $request): string
    {
        return $request->attributes->get('user_type') ?? 'teacher';
    }

    /**
     * Format academic session data for responses.
     */
    protected function formatSession(?AcademicSession $session): ?array
    {
        if (! $session) {
            return null;
        }

        $startDate = $session->start_date instanceof \DateTimeInterface
            ? $session->start_date->format('Y-m-d')
            : (string) $session->start_date;

        $endDate = $session->end_date instanceof \DateTimeInterface
            ? $session->end_date->format('Y-m-d')
            : (string) $session->end_date;

        return [
            'id' => $session->id,
            'name' => $session->name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $session->status,
        ];
    }

    /**
     * Standard success JSON response.
     */
    protected function success(Request $request, $data = null, string $message = 'Success', int $status = 200, array $extra = []): JsonResponse
    {
        $session = $this->getSession($request);

        $payload = array_merge([
            'success' => true,
            'message' => $message,
            'active_session' => $this->formatSession($session),
            'data' => $data,
        ], $extra);

        return response()->json($payload, $status);
    }

    /**
     * Standard paginated JSON response.
     */
    protected function paginated(Request $request, LengthAwarePaginator $paginator, string $message = 'Success', ?callable $transformer = null): JsonResponse
    {
        $session = $this->getSession($request);

        $items = $transformer
            ? $paginator->getCollection()->map($transformer)->values()
            : $paginator->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'active_session' => $this->formatSession($session),
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ], 200);
    }

    /**
     * Standard error JSON response.
     */
    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
