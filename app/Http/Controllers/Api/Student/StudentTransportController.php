<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentTransportController extends BaseApiController
{
    /**
     * Student Assigned Transport Details API.
     */
    public function transport(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $route = $student->transportRoutes()->with(['vehicles'])->first();

        if (! $route) {
            // Check first route in school as sample/fallback
            $route = TransportRoute::with(['vehicles'])->where('school_id', $school?->id)->first();
        }

        $vehicle = $route?->vehicles?->first();

        $data = [
            'is_assigned' => $route !== null,
            'route_id' => $route?->id,
            'route_name' => $route?->name ?? 'Route Not Assigned',
            'start_point' => $route?->start_point ?? '',
            'end_point' => $route?->end_point ?? '',
            'vehicle_id' => $vehicle?->id,
            'vehicle_number' => $vehicle?->vehicle_number ?? 'School Bus',
            'vehicle_model' => $vehicle?->model ?? 'Standard Bus',
            'driver_name' => $vehicle?->driver_name ?? 'Designated Driver',
            'driver_phone' => $vehicle?->driver_phone ?? '',
            'driver_license' => $vehicle?->driver_license ?? '',
            'pickup_point' => $student->address ?: 'Home Address Stop',
            'pickup_time' => '07:30 AM',
            'drop_time' => '02:30 PM',
            'monthly_fare' => (float) ($route?->fare ?? 0),
        ];

        return $this->success($request, ['transport' => $data], 'Transport details retrieved successfully');
    }
}
