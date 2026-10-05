<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\LibraryBook;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\Student;
use App\Models\StudentLeave;
use App\Models\SystemSetting;
use App\Models\TransportInvoice;
use App\Models\TransportPayment;
use Illuminate\Http\Request;

class SMSchoolController extends Controller
{
    public function dashboard(Request $request)
    {
        $schoolId = session('selected_school_id');
        if (! $schoolId) {
            return redirect()->route('school-management.dashboard')->with('error', 'Please select a school first.');
        }

        $school = School::findOrFail($schoolId);

        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();

        // Determine the session to view data for — priority: URL param > session store > global current session
        $globalCurrentSessionId = SystemSetting::getSetting('current_session_id');

        $selectedSessionId = $request->query('session_id')
            ?: session('selected_session_id')
            ?: $globalCurrentSessionId;

        // Always persist so that ClassGroupController/SchoolClassController can read it
        session([
            'selected_session_id' => $selectedSessionId,
            'selected_academic_session_id' => $selectedSessionId
        ]);

        $currentSession = AcademicSession::find($selectedSessionId);
        $globalCurrentSession = AcademicSession::find($globalCurrentSessionId);

        $sid = $school->id;
        $sessId = $currentSession?->id;

        // ── Classes ──────────────────────────────────────────────────────────────
        $totalClasses = $sessId
            ? SchoolClass::where('school_id', $sid)->where('academic_session_id', $sessId)->count()
            : 0;

        // ── Students ─────────────────────────────────────────────────────────────
        $totalActiveStudents = Student::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->where('status', 'Active')
            ->count();

        // ── Staff ────────────────────────────────────────────────────────────────
        $totalStaff = Staff::where('school_id', $sid)->where('status', 'Active')->count();

        // ── Fee Invoices (school fees + transport) ───────────────────────────────
        $feeInvoices      = FeeInvoice::where('school_id', $sid)->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId));
        $transportInvoices = TransportInvoice::where('school_id', $sid)->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId));

        // Total generated invoice amount
        $totalIncome = (clone $feeInvoices)->sum('total_amount')
                     + (clone $transportInvoices)->sum('total_amount');

        // Total collected (actual payments received)
        $feesCollected = FeePayment::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->whereHas('invoice', fn ($iq) => $iq->where('academic_session_id', $sessId)))
            ->sum('amount')
            + TransportPayment::whereHas('invoice', fn ($q) => $q->where('school_id', $sid)->when($sessId, fn ($iq) => $iq->where('academic_session_id', $sessId)))
            ->sum('amount');

        // Pending dues = total - paid
        $pendingDues = ((clone $feeInvoices)->sum('total_amount') - (clone $feeInvoices)->sum('amount_paid'))
                     + ((clone $transportInvoices)->sum('total_amount') - (clone $transportInvoices)->sum('amount_paid'));

        // Unpaid invoices count (unpaid + partial)
        $unpaidInvoices = (clone $feeInvoices)->whereIn('status', ['unpaid', 'partial'])->count()
                        + (clone $transportInvoices)->whereIn('status', ['unpaid', 'partial'])->count();

        // ── Expenses (salary paid out) ────────────────────────────────────────────
        // We use salary records as "expenses"
        $totalExpenses = \App\Models\StaffSalaryRecord::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->sum('net_salary');

        // ── Library ──────────────────────────────────────────────────────────────
        $totalBooks = LibraryBook::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->sum('quantity');

        // ── Leaves ───────────────────────────────────────────────────────────────
        $pendingStudentLeaves = StudentLeave::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('session_id', $sessId))
            ->where('status', 'Pending')
            ->count();

        $pendingStaffLeaves = StaffLeave::where('school_id', $sid)
            ->where('status', 'Pending')
            ->count();

        return view('sm-school.dashboard', compact(
            'sessions', 'currentSession', 'school', 'globalCurrentSession',
            'totalClasses', 'totalActiveStudents', 'totalStaff',
            'totalIncome', 'feesCollected', 'pendingDues', 'unpaidInvoices',
            'totalExpenses', 'totalBooks', 'pendingStudentLeaves', 'pendingStaffLeaves'
        ));
    }
}
