<?php

namespace App\Http\Controllers;

use App\Models\TransportInvoice;
use App\Models\TransportRoute;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\School;
use App\Models\AcademicSession;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class TransportInvoiceController extends Controller
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
        $invoices = TransportInvoice::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                $q->where('academic_session_id', $session->id);
            })
            ->with(['student', 'transportRoute'])
            ->get();
            
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();
        $authorizers = ['admin' => 'Admin'];
            
        return view('accounting.transport-invoices.index', compact('invoices', 'school', 'session', 'paymentMethods', 'authorizers'));
    }
    
    public function print(TransportInvoice $transportInvoice)
    {
        $school = $this->currentSchool();
        $transportInvoice->load(['student.schoolClass', 'student.section', 'transportRoute']);
        return view('accounting.transport-invoices.print-single', compact('transportInvoice', 'school'));
    }

    public function create(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $classes = SchoolClass::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('school_classes', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })->get();
            
        $sections = Section::whereIn('school_class_id', $classes->pluck('id'))->get();
        $routes = TransportRoute::where('school_id', $school->id)
            ->when($session, function($q) use ($session) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('transport_routes', 'academic_session_id')) {
                    $q->where('academic_session_id', $session->id);
                }
            })
            ->get();
            
        $students = collect();
        if ($request->class_id && $request->section_id && $request->transport_route_id) {
            $studentsQuery = Student::where('school_id', $school->id)
                ->where('school_class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->whereHas('transportRoutes', function($q) use ($request) {
                    $q->where('transport_routes.id', $request->transport_route_id);
                });
                
            if ($session && \Illuminate\Support\Facades\Schema::hasColumn('students', 'academic_session_id')) {
                $studentsQuery->where('academic_session_id', $session->id);
            }
            $students = $studentsQuery->get();
        }

        return view('accounting.transport-invoices.create', compact('classes', 'sections', 'routes', 'students', 'school', 'session', 'request'));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'transport_route_id' => 'required|exists:transport_routes,id',
            'invoice_title' => 'required|string',
            'issued_at' => 'required|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $students = Student::whereIn('id', $request->student_ids)->get();

        foreach ($students as $student) {
            TransportInvoice::create([
                'school_id' => $school->id,
                'academic_session_id' => $session ? $session->id : null,
                'student_id' => $student->id,
                'transport_route_id' => $request->transport_route_id,
                'invoice_title' => $request->invoice_title,
                'issued_at' => $request->issued_at,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_amount' => $request->amount,
                'amount_paid' => 0,
                'due_amount' => $request->amount,
                'status' => 'unpaid',
            ]);
        }

        return redirect()->route('accounting.transport-invoices.index')->with('success', 'Transport Invoices generated successfully.');
    }

    public function show(TransportInvoice $transportInvoice)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $transportInvoice->load(['student', 'transportRoute', 'payments']);
        $paymentMethods = \App\Models\PaymentMethod::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();
        return view('accounting.transport-invoices.show', compact('transportInvoice', 'school', 'session', 'paymentMethods'));
    }

    public function collect(Request $request, TransportInvoice $transportInvoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$transportInvoice->due_amount,
            'payment_date' => 'required|date',
        ]);

        $transportInvoice->payments()->create([
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
        ]);

        $transportInvoice->amount_paid += $request->amount;
        $transportInvoice->due_amount -= $request->amount;
        
        if ($transportInvoice->due_amount <= 0) {
            $transportInvoice->status = 'paid';
        } else {
            $transportInvoice->status = 'partial';
        }
        
        $transportInvoice->save();

        return back()->with('success', 'Payment collected successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $school = $this->currentSchool();
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No invoices selected.');
        }

        TransportInvoice::where('school_id', $school->id)->whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected transport invoices have been deleted.');
    }

    public function printSelected(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No invoices selected to print.');
        }

        $invoices = TransportInvoice::with(['student.schoolClass', 'student.section', 'transportRoute'])
            ->where('school_id', $school->id)
            ->whereIn('id', $ids)
            ->get();

        return view('accounting.transport-invoices.print-bulk', compact('invoices', 'school'));
    }
}
