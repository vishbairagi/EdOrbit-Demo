<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Staff;
use App\Models\StaffSalaryRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmStaffPayrollController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $sessionId = $request->input('session_id', session('selected_session_id'));
        $month = $request->input('month', today()->format('Y-m'));

        $staff = Staff::where('school_id', $schoolId)->where('status', 'Active')->get();
        $sessions = AcademicSession::all();
        $records = StaffSalaryRecord::where('school_id', $schoolId)
            ->where('month', $month)
            ->get()
            ->keyBy('staff_id');

        return view('sm-administrator.payroll.index', compact('staff', 'sessions', 'records', 'month', 'sessionId'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => 'required|string',
            'session_id' => 'required|exists:academic_sessions,id',
            'staff' => 'required|array',
        ]);

        $schoolId = session('selected_school_id');

        foreach ($request->input('staff') as $staffId => $data) {
            $staffModel = Staff::findOrFail($staffId);
            $net = ($data['basic'] ?? $staffModel->salary ?? 0)
                + ($data['allowances'] ?? 0)
                - ($data['deductions'] ?? 0);

            StaffSalaryRecord::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'staff_id' => $staffId,
                    'month' => $request->input('month'),
                ],
                [
                    'academic_session_id' => $request->input('session_id'),
                    'basic_salary' => $data['basic'] ?? $staffModel->salary ?? 0,
                    'allowances' => $data['allowances'] ?? 0,
                    'deductions' => $data['deductions'] ?? 0,
                    'net_salary' => $net,
                    'payment_status' => $data['payment_status'] ?? 'Pending',
                ]
            );
        }

        return back()->with('success', 'Payroll generated for '.$request->input('month').'.');
    }

    public function slip(Staff $staff, string $month): View
    {
        $schoolId = session('selected_school_id');
        $record = StaffSalaryRecord::where('school_id', $schoolId)
            ->where('staff_id', $staff->id)
            ->where('month', $month)
            ->firstOrFail();

        return view('sm-administrator.payroll.slip', compact('staff', 'record', 'month'));
    }
}
