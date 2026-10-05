<?php

namespace App\Http\Controllers;

use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Models\Student;
use App\Models\FeePeriod;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class TransportRouteController extends Controller
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
        $routes = TransportRoute::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->with(['period', 'vehicles'])
            ->get();
        return view('transport.routes.index', compact('routes', 'school', 'session'));
    }

    public function create()
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
            
        $periods = FeePeriod::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('fee_periods', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->get();
        return view('transport.routes.create', compact('vehicles', 'periods', 'school', 'session'));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        
        $request->validate([
            'route_name' => 'required|string|max:255',
            'route_fare' => 'required|numeric|min:0',
            'period_id' => 'required|exists:fee_periods,id',
            'vehicles' => 'array',
        ]);

        $data = [
            'school_id' => $school->id,
            'route_name' => $request->route_name,
            'route_fare' => $request->route_fare,
            'period_id' => $request->period_id,
        ];
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id') && $session) {
            $data['academic_session_id'] = $session->id;
        }

        $route = TransportRoute::create($data);

        if ($request->has('vehicles')) {
            $route->vehicles()->sync($request->vehicles);
        }

        return redirect()->route('transport.routes.index')->with('success', 'Transport Route added successfully.');
    }

    public function edit(TransportRoute $route)
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
            
        $periods = FeePeriod::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('fee_periods', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->get();
        return view('transport.routes.edit', compact('route', 'vehicles', 'periods', 'school', 'session'));
    }

    public function update(Request $request, TransportRoute $route)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'route_fare' => 'required|numeric|min:0',
            'period_id' => 'required|exists:fee_periods,id',
            'vehicles' => 'array',
        ]);

        $route->update([
            'route_name' => $request->route_name,
            'route_fare' => $request->route_fare,
            'period_id' => $request->period_id,
        ]);

        $route->vehicles()->sync($request->vehicles ?? []);

        return redirect()->route('transport.routes.index')->with('success', 'Transport Route updated successfully.');
    }

    public function destroy(TransportRoute $route)
    {
        $route->delete();
        return redirect()->route('transport.routes.index')->with('success', 'Transport Route deleted successfully.');
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\TransportRoute::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}