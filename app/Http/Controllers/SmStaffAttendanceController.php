<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmStaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $date = $request->input('date', today()->toDateString());

        $staff = Staff::where('school_id', $schoolId)->where('status', 'Active')->get();
        $attendances = StaffAttendance::where('school_id', $schoolId)
            ->where('date', $date)
            ->get()
            ->keyBy('staff_id');

        return view('sm-administrator.attendance.index', compact('staff', 'attendances', 'date'));
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.staff_id' => 'required|exists:staff,id',
            'attendances.*.status' => 'required|in:Present,Absent,Late,Half Day,On Leave',
        ]);

        $schoolId = session('selected_school_id');

        $dateString = $request->input('date');

        foreach ($request->input('attendances') as $row) {
            $attendance = StaffAttendance::where('staff_id', $row['staff_id'])
                ->whereDate('date', $dateString)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'school_id' => $schoolId,
                    'status' => $row['status'],
                    'note' => $row['note'] ?? null
                ]);
            } else {
                StaffAttendance::create([
                    'staff_id' => $row['staff_id'],
                    'date' => $dateString,
                    'school_id' => $schoolId,
                    'status' => $row['status'],
                    'note' => $row['note'] ?? null
                ]);
            }
        }

        return back()->with('success', 'Attendance saved for '.$request->input('date').'.');
    }

    public function report(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $month = $request->input('month', today()->format('Y-m'));
        $staffId = $request->input('staff_id');

        $staff = Staff::where('school_id', $schoolId)->get();
        $query = StaffAttendance::where('school_id', $schoolId)
            ->whereRaw("strftime('%Y-%m', date) = ?", [$month]);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $attendances = $query->get()->groupBy('staff_id');

        return view('sm-administrator.attendance.report', compact('staff', 'attendances', 'month', 'staffId'));
    }
}
