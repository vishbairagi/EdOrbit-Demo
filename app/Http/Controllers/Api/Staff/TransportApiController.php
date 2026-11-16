<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportApiController extends BaseApiController
{
    // ==========================================
    // 1. VEHICLES CRUD (transport.vehicles)
    // ==========================================

    public function vehiclesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = Vehicle::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('driver_phone', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('vehicle_number')->get(), 'Vehicles retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->orderBy('vehicle_number')->paginate($perPage), 'Vehicles retrieved');
    }

    public function vehicleStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
        ]);

        $vehicle = Vehicle::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'vehicle_number' => $validated['vehicle_number'],
            'vehicle_model' => $validated['vehicle_model'] ?? null,
            'driver_name' => $validated['driver_name'] ?? null,
            'driver_phone' => $validated['driver_phone'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        return $this->success($request, $vehicle, 'Vehicle created successfully', 201);
    }

    public function vehicleShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $vehicle = Vehicle::with('transportRoutes')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $vehicle) {
            return $this->error('Vehicle not found.', 404);
        }

        return $this->success($request, $vehicle, 'Vehicle details retrieved');
    }

    public function vehicleUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $vehicle = Vehicle::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $vehicle) {
            return $this->error('Vehicle not found.', 404);
        }

        $validated = $request->validate([
            'vehicle_number' => 'sometimes|required|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
        ]);

        $vehicle->update($validated);

        return $this->success($request, $vehicle, 'Vehicle updated successfully');
    }

    public function vehicleDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $vehicle = Vehicle::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $vehicle) {
            return $this->error('Vehicle not found.', 404);
        }

        $vehicle->transportRoutes()->detach();
        $vehicle->delete();

        return $this->success($request, null, 'Vehicle deleted successfully');
    }

    // ==========================================
    // 2. ROUTES CRUD (transport.routes)
    // ==========================================

    public function routesIndex(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $query = TransportRoute::with(['vehicles', 'period'])
            ->withCount('students')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id);

        if ($search = trim($request->input('search', ''))) {
            $query->where('route_name', 'like', "%{$search}%");
        }

        if ($request->boolean('all') || $request->input('per_page') == -1) {
            return $this->success($request, $query->orderBy('route_name')->get(), 'Transport routes retrieved');
        }

        $perPage = (int) $request->input('per_page', 10);
        return $this->paginated($request, $query->orderBy('route_name')->paginate($perPage), 'Transport routes retrieved');
    }

    public function routeStore(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'route_fare' => 'required|numeric|min:0',
            'period_id' => 'nullable|exists:fee_periods,id',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $route = TransportRoute::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'route_name' => $validated['route_name'],
            'route_fare' => $validated['route_fare'],
            'period_id' => $validated['period_id'] ?? null,
        ]);

        if (! empty($validated['vehicle_ids'])) {
            $route->vehicles()->sync($validated['vehicle_ids']);
        }

        $route->load(['vehicles', 'period']);

        return $this->success($request, $route, 'Transport route created successfully', 201);
    }

    public function routeShow(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $route = TransportRoute::with(['vehicles', 'students', 'period'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $route) {
            return $this->error('Transport route not found.', 404);
        }

        return $this->success($request, $route, 'Transport route details retrieved');
    }

    public function routeUpdate(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $route = TransportRoute::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $route) {
            return $this->error('Transport route not found.', 404);
        }

        $validated = $request->validate([
            'route_name' => 'sometimes|required|string|max:255',
            'route_fare' => 'sometimes|required|numeric|min:0',
            'period_id' => 'nullable|exists:fee_periods,id',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $route->update($validated);

        if (isset($validated['vehicle_ids'])) {
            $route->vehicles()->sync($validated['vehicle_ids']);
        }

        $route->load(['vehicles', 'period']);

        return $this->success($request, $route, 'Transport route updated successfully');
    }

    public function routeDestroy(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $route = TransportRoute::where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->find($id);

        if (! $route) {
            return $this->error('Transport route not found.', 404);
        }

        $route->vehicles()->detach();
        $route->students()->detach();
        $route->delete();

        return $this->success($request, null, 'Transport route deleted successfully');
    }

    // ==========================================
    // 3. ASSIGN STUDENTS (transport.assign-students)
    // ==========================================

    public function assignStudentToRoute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $route = TransportRoute::findOrFail($validated['route_id']);
        $route->students()->syncWithoutDetaching([$validated['student_id']]);

        return $this->success($request, $route->load('students'), 'Student assigned to route successfully');
    }

    public function removeStudentFromRoute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $route = TransportRoute::findOrFail($validated['route_id']);
        $route->students()->detach([$validated['student_id']]);

        return $this->success($request, null, 'Student removed from route successfully');
    }
}
