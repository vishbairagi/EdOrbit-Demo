<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\LibraryBook;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Section;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffSalaryRecord;
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
            'selected_academic_session_id' => $selectedSessionId,
        ]);

        $currentSession = AcademicSession::find($selectedSessionId);
        $globalCurrentSession = AcademicSession::find($globalCurrentSessionId);

        $sid = $school->id;
        $sessId = $currentSession?->id;

        // ── 1. Classes & Sections ────────────────────────────────────────────────
        $totalClasses = $sessId
            ? SchoolClass::where('school_id', $sid)->where('academic_session_id', $sessId)->count()
            : SchoolClass::where('school_id', $sid)->count();

        $totalSections = Section::whereHas('schoolClass', function ($q) use ($sid, $sessId) {
            $q->where('school_id', $sid);
            if ($sessId) {
                $q->where('academic_session_id', $sessId);
            }
        })->count();

        // ── 2. Students & Active/Inactive ─────────────────────────────────────────
        $studentBaseQuery = Student::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId));

        $totalStudents = (clone $studentBaseQuery)->count();
        $totalActiveStudents = (clone $studentBaseQuery)->where('status', 'Active')->count();
        $totalInactiveStudents = max(0, $totalStudents - $totalActiveStudents);
        $activeStudentsPercent = $totalStudents > 0 ? round(($totalActiveStudents / $totalStudents) * 100, 1) : 0;

        $maleStudents = (clone $studentBaseQuery)->where('gender', 'Male')->count();
        $femaleStudents = (clone $studentBaseQuery)->where('gender', 'Female')->count();
        $otherStudents = max(0, $totalStudents - $maleStudents - $femaleStudents);

        // Class-wise student distribution for donut/circular chart
        $classesWithStudents = SchoolClass::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->withCount('students')
            ->orderByDesc('students_count')
            ->take(6)
            ->get();

        // ── 3. Staff & Staff with Username ────────────────────────────────────────
        $staffBase = Staff::with('role')->where('school_id', $sid)->get();

        $totalStaff = $staffBase->count();
        $activeStaff = $staffBase->filter(function ($s) {
            $st = strtolower((string) $s->status);
            return $st === 'active' || $st === '1';
        })->count();
        $inactiveStaff = max(0, $totalStaff - $activeStaff);
        $activeStaffPercent = $totalStaff > 0 ? round(($activeStaff / $totalStaff) * 100, 1) : 0;

        // Group staff by roles for circular chart
        $staffByRole = $staffBase->groupBy(function ($s) {
            return $s->role?->name ?: ($s->designation ?: 'Staff');
        })->map->count();

        // Paginated staff list for "Staff Directory & Username Credentials" table
        $staffSearch = trim((string) $request->query('staff_search'));
        $staffQuery = Staff::with('role')->where('school_id', $sid);

        if ($staffSearch !== '') {
            $staffQuery->where(function ($q) use ($staffSearch) {
                $q->where('name', 'like', "%{$staffSearch}%")
                    ->orWhere('username', 'like', "%{$staffSearch}%")
                    ->orWhere('login_email', 'like', "%{$staffSearch}%")
                    ->orWhere('email', 'like', "%{$staffSearch}%")
                    ->orWhere('employee_id', 'like', "%{$staffSearch}%")
                    ->orWhere('phone', 'like', "%{$staffSearch}%")
                    ->orWhereHas('role', function ($rq) use ($staffSearch) {
                        $rq->where('name', 'like', "%{$staffSearch}%");
                    });
            });
        }

        $allStaff = $staffQuery->orderBy('id', 'asc')
            ->paginate(10)
            ->fragment('staffDirectory')
            ->withQueryString();

        // ── 4. Fee Invoices (school fees + transport) ─────────────────────────────
        $feeInvoices = FeeInvoice::where('school_id', $sid)->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId));
        $transportInvoices = TransportInvoice::where('school_id', $sid)->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId));

        $feeInvoiced = (float) (clone $feeInvoices)->sum('total_amount');
        $feePaid = (float) (clone $feeInvoices)->sum('amount_paid');
        $transportInvoiced = (float) (clone $transportInvoices)->sum('total_amount');
        $transportPaid = (float) (clone $transportInvoices)->sum('amount_paid');

        $totalIncome = $feeInvoiced + $transportInvoiced;
        $feesCollected = $feePaid + $transportPaid;
        $pendingDues = max(0, $totalIncome - $feesCollected);

        $collectionRate = $totalIncome > 0 ? round(($feesCollected / $totalIncome) * 100, 1) : 0;
        $pendingRate = $totalIncome > 0 ? round(($pendingDues / $totalIncome) * 100, 1) : 0;

        $unpaidInvoices = (clone $feeInvoices)->whereIn('status', ['unpaid', 'partial'])->count()
            + (clone $transportInvoices)->whereIn('status', ['unpaid', 'partial'])->count();

        $paidInvoicesCount = (clone $feeInvoices)->where('status', 'paid')->count()
            + (clone $transportInvoices)->where('status', 'paid')->count();
        $partialInvoicesCount = (clone $feeInvoices)->where('status', 'partial')->count()
            + (clone $transportInvoices)->where('status', 'partial')->count();
        $rawUnpaidCount = (clone $feeInvoices)->where('status', 'unpaid')->count()
            + (clone $transportInvoices)->where('status', 'unpaid')->count();

        // ── 5. Expenses (salary paid out) ─────────────────────────────────────────
        $totalExpenses = (float) StaffSalaryRecord::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->sum('net_salary');

        // ── 6. Library ────────────────────────────────────────────────────────────
        $totalBooks = LibraryBook::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('academic_session_id', $sessId))
            ->sum('quantity');

        // ── 7. Leaves ─────────────────────────────────────────────────────────────
        $studentLeavesBase = StudentLeave::where('school_id', $sid)
            ->when($sessId, fn ($q) => $q->where('session_id', $sessId));

        $pendingStudentLeaves = (clone $studentLeavesBase)->where('status', 'Pending')->count();
        $approvedStudentLeaves = (clone $studentLeavesBase)->where('status', 'Approved')->count();
        $rejectedStudentLeaves = (clone $studentLeavesBase)->where('status', 'Rejected')->count();

        $staffLeavesBase = StaffLeave::where('school_id', $sid);

        $pendingStaffLeaves = (clone $staffLeavesBase)->where('status', 'Pending')->count();
        $approvedStaffLeaves = (clone $staffLeavesBase)->where('status', 'Approved')->count();
        $rejectedStaffLeaves = (clone $staffLeavesBase)->where('status', 'Rejected')->count();

        $totalApprovedLeaves = $approvedStudentLeaves + $approvedStaffLeaves;
        $totalPendingLeaves = $pendingStudentLeaves + $pendingStaffLeaves;
        $totalRejectedLeaves = $rejectedStudentLeaves + $rejectedStaffLeaves;
        $totalAllLeaves = $totalApprovedLeaves + $totalPendingLeaves + $totalRejectedLeaves;

        $currencySymbol = SchoolSetting::getCurrencySymbol();

        return view('sm-school.dashboard', compact(
            'sessions',
            'currentSession',
            'school',
            'globalCurrentSession',
            'totalClasses',
            'totalSections',
            'totalStudents',
            'totalActiveStudents',
            'totalInactiveStudents',
            'activeStudentsPercent',
            'maleStudents',
            'femaleStudents',
            'otherStudents',
            'classesWithStudents',
            'allStaff',
            'totalStaff',
            'activeStaff',
            'inactiveStaff',
            'activeStaffPercent',
            'staffByRole',
            'totalIncome',
            'feesCollected',
            'pendingDues',
            'collectionRate',
            'pendingRate',
            'unpaidInvoices',
            'paidInvoicesCount',
            'partialInvoicesCount',
            'rawUnpaidCount',
            'feeInvoiced',
            'feePaid',
            'transportInvoiced',
            'transportPaid',
            'totalExpenses',
            'totalBooks',
            'pendingStudentLeaves',
            'approvedStudentLeaves',
            'rejectedStudentLeaves',
            'pendingStaffLeaves',
            'approvedStaffLeaves',
            'rejectedStaffLeaves',
            'totalApprovedLeaves',
            'totalPendingLeaves',
            'totalRejectedLeaves',
            'totalAllLeaves',
            'currencySymbol'
        ));
    }
}
