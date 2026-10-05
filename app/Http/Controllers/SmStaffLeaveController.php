<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffLeave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmStaffLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $status = $request->input('status', 'all');

        $query = StaffLeave::where('school_id', $schoolId)->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $leaves = $query->get()->map(function ($leave) {
            if ($leave->applicant_type === 'staff') {
                $leave->applicant_model = Staff::find($leave->applicant_id);
            } else {
                $leave->applicant_model = SchoolUser::find($leave->applicant_id);
            }
            return $leave;
        });

        $allStaff = Staff::where('school_id', $schoolId)->get();
        $allAdmins = SchoolUser::where('school_id', $schoolId)->get();

        $users = collect();
        foreach ($allAdmins as $admin) {
            $users->push((object)['id' => $admin->id, 'type' => 'school_user', 'name' => $admin->name . ' (Admin)']);
        }
        foreach ($allStaff as $staff) {
            $users->push((object)['id' => $staff->id, 'type' => 'staff', 'name' => $staff->name . ' (Staff)']);
        }

        return view('sm-administrator.leaves.index', compact('leaves', 'status', 'users'));
    }

    public function update(Request $request, StaffLeave $leave): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Approved,Rejected',
            'admin_note' => 'nullable|string',
        ]);

        $leave->update([
            'status' => $request->input('status'),
            'admin_note' => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave status updated.');
    }

    // Staff/Admin applying for leave
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_type' => 'required|string',
            'reason' => 'required|string',
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
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Leave application submitted.');
    }

    // Admin adding leave for someone else
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_type' => 'required|string',
            'reason' => 'required|string',
            'status' => 'required|in:Pending,Processing,Approved,Rejected',
        ]);

        $schoolId = session('selected_school_id');
        
        $parts = explode('_', $request->input('applicant'));
        $applicantId = array_pop($parts);
        $applicantType = implode('_', $parts);

        StaffLeave::create([
            'school_id' => $schoolId,
            'applicant_id' => $applicantId,
            'applicant_type' => $applicantType,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'leave_type' => $request->input('leave_type'),
            'reason' => $request->input('reason'),
            'status' => $request->input('status'),
            'admin_note' => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave created successfully.');
    }
}
