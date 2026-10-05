<?php

namespace App\Http\Controllers;

use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\TransportInvoice;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class TransportController extends Controller
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

    public function dashboard()
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $vehiclesQuery = Vehicle::where('school_id', $school->id);
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('vehicles', 'academic_session_id')) {
            $vehiclesQuery->where('academic_session_id', $session->id);
        }
        $vehiclesCount = $vehiclesQuery->count();

        $routesQuery = TransportRoute::where('school_id', $school->id);
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id')) {
            $routesQuery->where('academic_session_id', $session->id);
        }
        $routesCount = $routesQuery->count();
        
        $studentsQuery = Student::where('school_id', $school->id)->whereHas('transportRoutes');
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('students', 'academic_session_id')) {
            $studentsQuery->where('academic_session_id', $session->id);
        }
        $studentsCount = $studentsQuery->count();
            
        $invoicesQuery = TransportInvoice::where('school_id', $school->id);
        if ($session) {
            $invoicesQuery->where('academic_session_id', $session->id);
        }
        $invoicesCount = $invoicesQuery->count();

        return view('transport.dashboard', compact('vehiclesCount', 'routesCount', 'studentsCount', 'invoicesCount', 'school', 'session'));
    }

    public function report(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        
        $classes = SchoolClass::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('school_classes', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->get();
            
        $sections = Section::whereIn('school_class_id', $classes->pluck('id'))->get();
        $routesQuery = TransportRoute::where('school_id', $school->id);
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id')) {
            $routesQuery->where('academic_session_id', $session->id);
        }
        $routes = $routesQuery->get();
        
        $students = collect();
        if ($request->has('route_id') && $request->route_id != '') {
            $query = Student::where('school_id', $school->id)
                ->whereHas('transportRoutes', function($q) use ($request) {
                    $q->where('transport_routes.id', $request->route_id);
                });
                
            if ($session && \Illuminate\Support\Facades\Schema::hasColumn('students', 'academic_session_id')) {
                $query->where('academic_session_id', $session->id);
            }
                
            if ($request->class_id) {
                $query->where('school_class_id', $request->class_id);
            }
            if ($request->section_id) {
                $query->where('section_id', $request->section_id);
            }
            
            $students = $query->with(['schoolClass', 'section', 'transportRoutes'])->get();
        }

        return view('transport.report', compact('classes', 'sections', 'routes', 'students', 'request', 'school', 'session'));
    }
}
