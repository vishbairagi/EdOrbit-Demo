<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffLeave;
use App\Models\StaffSalaryRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $guard = session('auth_guard');
        $userId = session('auth_user_id');

        if ($guard === 'staff') {
            $person = Staff::with('role')->findOrFail($userId);
            $type = 'staff';
        } else {
            $person = SchoolUser::findOrFail($userId);
            $type = 'school_user';
        }

        $schoolId = $person->school_id;
        $todayDate = today()->toDateString();

        // Attendance summary (current month)
        $month = today()->format('Y-m');
        $attendanceSummary = [];

        if ($type === 'staff') {
            $attendanceSummary = StaffAttendance::where('staff_id', $userId)
                ->whereRaw("strftime('%Y-%m', date) = ?", [$month])
                ->get()
                ->groupBy('status')
                ->map->count();
        }

        // Recent leaves
        $leaveQuery = StaffLeave::where('school_id', $schoolId)
            ->where('applicant_id', $userId)
            ->where('applicant_type', $type)
            ->latest()
            ->take(5);

        $recentLeaves = $leaveQuery->get();

        // Salary records (last 6 months)
        $salaryRecords = collect();
        if ($type === 'staff') {
            $salaryRecords = StaffSalaryRecord::where('staff_id', $userId)
                ->orderBy('month', 'desc')
                ->take(6)
                ->get();
        }

        // Upcoming leave applications (pending)
        $pendingLeaves = StaffLeave::where('school_id', $schoolId)
            ->where('applicant_id', $userId)
            ->where('applicant_type', $type)
            ->where('status', 'Pending')
            ->count();

        return view('staff.dashboard', compact(
            'person',
            'type',
            'attendanceSummary',
            'recentLeaves',
            'salaryRecords',
            'pendingLeaves',
        ));
    }

    public function idCard(): View
    {
        $guard = session('auth_guard');
        $userId = session('auth_user_id');

        if ($guard === 'staff') {
            $person = Staff::with('role')->findOrFail($userId);
        } else {
            $person = SchoolUser::findOrFail($userId);
        }

        return view('staff.id-card', compact('person'));
    }

    public function applyLeave(Request $request): RedirectResponse
    {
        $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_type' => 'required|string',
            'reason' => 'required|string|min:10',
        ]);

        $guard = session('auth_guard');
        $userId = session('auth_user_id');
        $schoolId = session('selected_school_id');

        StaffLeave::create([
            'school_id' => $schoolId,
            'applicant_id' => $userId,
            'applicant_type' => $guard === 'staff' ? 'staff' : 'school_user',
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'leave_type' => $request->input('leave_type'),
            'reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Leave application submitted successfully.');
    }
}
