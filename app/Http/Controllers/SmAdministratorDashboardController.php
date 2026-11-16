<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffRole;
use App\Models\StaffSalaryRecord;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmAdministratorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $school = $schoolId ? School::find($schoolId) : School::first();
        $sid = $school ? $school->id : $schoolId;

        if ($request->filled('session_id')) {
            session([
                'selected_session_id' => $request->session_id,
                'selected_academic_session_id' => $request->session_id,
            ]);
        }

        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        $session = $sessionId ? AcademicSession::find($sessionId) : null;
        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $globalCurrentSessionId = SystemSetting::getSetting('current_session_id');
        $globalCurrentSession = AcademicSession::find($globalCurrentSessionId);

        // ── 1. Administrators & Leadership ──────────────────────────────────
        $admins = SchoolUser::where('school_id', $sid)->latest()->get();
        $adminsCount = $admins->count();

        $adminRolesGrouped = $admins->groupBy(fn ($a) => $a->role ?: 'Admin');
        $adminRolesData = [];
        $aColors = ['#8B5CF6', '#B47A42', '#3B82F6', '#10B981', '#EC4899', '#F59E0B'];
        $aIdx = 0;
        foreach ($adminRolesGrouped as $admRole => $items) {
            $cnt = $items->count();
            $pct = $adminsCount > 0 ? round(($cnt / $adminsCount) * 100, 1) : 0;
            $adminRolesData[] = [
                'name' => $admRole,
                'count' => $cnt,
                'percentage' => $pct,
                'color' => $aColors[$aIdx % count($aColors)],
            ];
            $aIdx++;
        }

        // ── 2. Staff Roles & Departments ────────────────────────────────────
        $roles = StaffRole::where('school_id', $sid)->get();
        $rolesCount = $roles->count();

        $staff = Staff::with('role')->where('school_id', $sid)->get();
        $totalStaff = $staff->count();
        $activeStaffCount = $staff->filter(fn ($s) => strtolower((string) $s->status) === 'active' || $s->status == '1')->count();
        $inactiveStaffCount = max(0, $totalStaff - $activeStaffCount);
        $activeStaffRate = $totalStaff > 0 ? round(($activeStaffCount / $totalStaff) * 100, 1) : 0;

        $palette = ['#B47A42', '#3B82F6', '#10B981', '#8B5CF6', '#EC4899', '#F59E0B', '#06B6D4', '#6366F1'];
        $staffRoleGrouped = $staff->groupBy(fn ($s) => $s->role?->name ?: 'Staff / General');
        $staffRolesData = [];
        $rIdx = 0;
        foreach ($staffRoleGrouped as $roleName => $items) {
            $cnt = $items->count();
            $pct = $totalStaff > 0 ? round(($cnt / $totalStaff) * 100, 1) : 0;
            $staffRolesData[] = [
                'name' => $roleName,
                'count' => $cnt,
                'percentage' => $pct,
                'color' => $palette[$rIdx % count($palette)],
            ];
            $rIdx++;
        }

        // ── 3. Leaves Resolution Pipeline ───────────────────────────────────
        $leaves = StaffLeave::where('school_id', $sid)->get();
        $totalLeaves = $leaves->count();
        $pendingLeaves = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'pending')->count();
        $processingLeaves = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'processing')->count();
        $approvedLeaves = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'approved')->count();
        $rejectedLeaves = $leaves->filter(fn ($l) => strtolower((string) $l->status) === 'rejected')->count();
        $leavesApprovalRate = $totalLeaves > 0 ? round(($approvedLeaves / $totalLeaves) * 100, 1) : 0;

        // ── 4. System Users & Credentials ───────────────────────────────────
        $totalUsers = $adminsCount + $totalStaff;
        $adminLogins = $admins->filter(fn ($a) => ! empty($a->username) || ! empty($a->login_email))->count();
        $staffLogins = $staff->filter(fn ($s) => ! empty($s->username) || ! empty($s->login_email))->count();
        $usersWithLoginCount = $adminLogins + $staffLogins;
        $credentialCoverageRate = $totalUsers > 0 ? round(($usersWithLoginCount / $totalUsers) * 100, 1) : 0;

        // User Types Distribution (Leadership vs Staff)
        $adminUsersPct = $totalUsers > 0 ? round(($adminsCount / $totalUsers) * 100, 1) : 0;
        $staffUsersPct = $totalUsers > 0 ? round(($totalStaff / $totalUsers) * 100, 1) : 0;

        // ── 5. Operations & Tables ──────────────────────────────────────────
        $payrollRecordsCount = StaffSalaryRecord::where('school_id', $sid)->count();
        $recentStaff = $staff->sortByDesc('id')->take(6);
        $recentAdmins = $admins->take(4);

        return view('sm-administrator.dashboard', compact(
            'school',
            'session',
            'sessions',
            'globalCurrentSession',
            'adminsCount',
            'adminRolesData',
            'rolesCount',
            'staff',
            'totalStaff',
            'activeStaffCount',
            'inactiveStaffCount',
            'activeStaffRate',
            'staffRolesData',
            'totalLeaves',
            'pendingLeaves',
            'processingLeaves',
            'approvedLeaves',
            'rejectedLeaves',
            'leavesApprovalRate',
            'totalUsers',
            'usersWithLoginCount',
            'credentialCoverageRate',
            'adminUsersPct',
            'staffUsersPct',
            'payrollRecordsCount',
            'recentStaff',
            'recentAdmins'
        ));
    }
}
