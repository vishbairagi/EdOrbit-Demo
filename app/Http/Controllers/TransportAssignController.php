<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class TransportAssignController extends Controller
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

    public function index(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        // Get filter inputs
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        
        $query = Student::where('school_id', $school->id);
        
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('students', 'academic_session_id')) {
            $query->where('academic_session_id', $session->id);
        }

        if ($classId) {
            $query->where('school_class_id', $classId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->with(['schoolClass', 'section', 'transportRoutes'])->paginate(20)->appends($request->all());

        // Get routes for the modal dropdown
        $routesQuery = TransportRoute::where('school_id', $school->id);
        if ($session && \Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id')) {
            $routesQuery->where('academic_session_id', $session->id);
        }
        $routes = $routesQuery->get();

        // Get classes for filter
        $classes = \App\Models\SchoolClass::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('school_classes', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })->get();

        // Get sections for filter
        $sections = \App\Models\Section::whereIn('school_class_id', $classes->pluck('id'))->get();

        return view('transport.assign.index', compact('students', 'routes', 'classes', 'sections', 'school', 'session'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id'
        ]);

        $students = Student::whereIn('id', $request->ids)->get();
        foreach ($students as $student) {
            $student->transportRoutes()->detach();
            \App\Models\TransportInvoice::where('student_id', $student->id)->delete();
        }

        return back()->with('success', 'Selected students unassigned successfully and related transport invoices removed.');
    }

    public function assign(Request $request, Student $student)
    {
        $request->validate([
            'route_id' => 'nullable|exists:transport_routes,id'
        ]);

        if ($request->route_id) {
            $student->transportRoutes()->sync([$request->route_id]);
        } else {
            $student->transportRoutes()->detach();
            \App\Models\TransportInvoice::where('student_id', $student->id)->delete();
        }

        return back()->with('success', 'Route assigned/unassigned successfully.');
    }
}
