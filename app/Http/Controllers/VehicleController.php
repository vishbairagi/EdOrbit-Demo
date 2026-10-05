<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id') ?? auth()->user()->school_id ?? 1;
        return $id ? School::find($id) : null;
    }

    private function currentSession(): ?AcademicSession
    {
        $id = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        return $id ? AcademicSession::find($id) : null;
    }

    public function index()
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $vehicles = Vehicle::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('vehicles', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->get();
        return view('transport.vehicles.index', compact('vehicles', 'school', 'session'));
    }

    public function create()
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        return view('transport.vehicles.create', compact('school', 'session'));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $request->validate([
            'vehicle_number' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'license_number' => 'nullable|string|max:255',
        ]);

        $data = [
            'school_id' => $school->id,
            'vehicle_number' => $request->vehicle_number,
            'vehicle_model' => $request->vehicle_model,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'note' => $request->note,
            'license_number' => $request->license_number,
        ];
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('vehicles', 'academic_session_id') && $session) {
            $data['academic_session_id'] = $session->id;
        }

        Vehicle::create($data);

        return redirect()->route('transport.vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        return view('transport.vehicles.edit', compact('vehicle', 'school', 'session'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vehicle_number' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'license_number' => 'nullable|string|max:255',
        ]);

        $vehicle->update($request->all());

        return redirect()->route('transport.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('transport.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\Vehicle::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}