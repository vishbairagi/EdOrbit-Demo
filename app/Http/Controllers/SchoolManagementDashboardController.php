<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\TransportInvoice;

class SchoolManagementDashboardController extends Controller
{
    public function index()
    {
        // ── 1. Schools & Breakdown ──
        $schools = School::withCount([
            'students',
            'students as active_students_count' => function ($q) {
                $q->where('status', 'Active');
            },
            'staff',
            'classes',
        ])->get();

        $totalSchools = $schools->count();
        $activeSchools = $schools->filter(function ($s) {
            $st = strtolower((string) $s->status);
            return $st === 'active' || $st === '1';
        })->count();
        $inactiveSchools = max(0, $totalSchools - $activeSchools);
        $activeSchoolsPercent = $totalSchools > 0 ? round(($activeSchools / $totalSchools) * 100, 1) : 0;

        // ── 2. Students Distribution Across Schools (kis school me kitne student h) ──
        $totalStudents = Student::count();
        $totalActiveStudents = Student::where('status', 'Active')->count();
        $totalInactiveStudents = max(0, $totalStudents - $totalActiveStudents);
        $activeStudentPercent = $totalStudents > 0 ? round(($totalActiveStudents / $totalStudents) * 100, 1) : 0;

        // Palette for distinct school circular rings/donuts
        $palette = ['#B47A42', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#14B8A6'];

        $schoolDistribution = $schools->map(function ($s, $idx) use ($totalStudents, $palette) {
            $studentCount = (int) $s->students_count;
            $percent = $totalStudents > 0 ? round(($studentCount / $totalStudents) * 100, 1) : 0;
            $st = strtolower((string) $s->status);
            $isActive = ($st === 'active' || $st === '1');

            return [
                'id' => $s->id,
                'name' => $s->name,
                'status' => $isActive ? 'active' : 'inactive',
                'is_active' => $isActive,
                'students_count' => $studentCount,
                'active_students_count' => (int) $s->active_students_count,
                'inactive_students_count' => max(0, $studentCount - (int) $s->active_students_count),
                'classes_count' => (int) $s->classes_count,
                'staff_count' => (int) $s->staff_count,
                'percentage' => $percent,
                'color' => $palette[$idx % count($palette)],
            ];
        });

        // ── 3. Academic Sessions & Active / Inactive Status ──
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $totalSessions = $sessions->count();
        $activeSessions = $sessions->filter(function ($ses) {
            $st = strtolower((string) $ses->status);
            return $st === 'active' || $st === '1';
        })->count();
        $inactiveSessions = max(0, $totalSessions - $activeSessions);
        $activeSessionsPercent = $totalSessions > 0 ? round(($activeSessions / $totalSessions) * 100, 1) : 0;

        $currentSessionId = SystemSetting::getSetting('current_session_id');
        $currentSession = AcademicSession::find($currentSessionId) 
            ?? $sessions->firstWhere('status', 'active') 
            ?? $sessions->first();

        // ── 4. Accounting & Financial Overview (accounting type me) ──
        $feeInvoiced = (float) FeeInvoice::sum('total_amount');
        $feePaid = (float) FeeInvoice::sum('amount_paid');
        $transportInvoiced = (float) TransportInvoice::sum('total_amount');
        $transportPaid = (float) TransportInvoice::sum('amount_paid');

        $totalInvoiced = $feeInvoiced + $transportInvoiced;
        $totalCollected = $feePaid + $transportPaid;
        $totalPending = max(0, $totalInvoiced - $totalCollected);

        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0;
        $pendingRate = $totalInvoiced > 0 ? round(($totalPending / $totalInvoiced) * 100, 1) : 0;

        // Invoices by status counts
        $paidInvoicesCount = FeeInvoice::where('status', 'paid')->count() + TransportInvoice::where('status', 'paid')->count();
        $partialInvoicesCount = FeeInvoice::where('status', 'partial')->count() + TransportInvoice::where('status', 'partial')->count();
        $unpaidInvoicesCount = FeeInvoice::where('status', 'unpaid')->count() + TransportInvoice::where('status', 'unpaid')->count();
        $totalInvoicesCount = $paidInvoicesCount + $partialInvoicesCount + $unpaidInvoicesCount;

        // School-wise financial breakdown
        $schoolFinancials = [];
        foreach ($schools as $s) {
            $sFeeInvoiced = (float) FeeInvoice::where('school_id', $s->id)->sum('total_amount');
            $sFeePaid = (float) FeeInvoice::where('school_id', $s->id)->sum('amount_paid');
            $sTransportInvoiced = (float) TransportInvoice::where('school_id', $s->id)->sum('total_amount');
            $sTransportPaid = (float) TransportInvoice::where('school_id', $s->id)->sum('amount_paid');

            $sInvoiced = $sFeeInvoiced + $sTransportInvoiced;
            $sCollected = $sFeePaid + $sTransportPaid;
            $sPending = max(0, $sInvoiced - $sCollected);
            $sRate = $sInvoiced > 0 ? round(($sCollected / $sInvoiced) * 100, 1) : 0;

            $schoolFinancials[] = [
                'school_id' => $s->id,
                'name' => $s->name,
                'invoiced' => $sInvoiced,
                'collected' => $sCollected,
                'pending' => $sPending,
                'rate' => $sRate,
            ];
        }

        // ── 5. Staff & Other Stats ──
        $totalStaff = Staff::count();
        $activeStaff = Staff::where('status', 'Active')->count();

        // Currency symbol
        $currencySymbol = SchoolSetting::getCurrencySymbol();

        // Recent schools list
        $recentSchools = School::latest()->take(6)->get();

        return view('school-management.dashboard', compact(
            'totalSchools',
            'activeSchools',
            'inactiveSchools',
            'activeSchoolsPercent',
            'totalStudents',
            'totalActiveStudents',
            'totalInactiveStudents',
            'activeStudentPercent',
            'schoolDistribution',
            'sessions',
            'totalSessions',
            'activeSessions',
            'inactiveSessions',
            'activeSessionsPercent',
            'currentSession',
            'totalInvoiced',
            'totalCollected',
            'totalPending',
            'collectionRate',
            'pendingRate',
            'paidInvoicesCount',
            'partialInvoicesCount',
            'unpaidInvoicesCount',
            'totalInvoicesCount',
            'schoolFinancials',
            'totalStaff',
            'activeStaff',
            'currencySymbol',
            'schools',
            'recentSchools'
        ));
    }
}
