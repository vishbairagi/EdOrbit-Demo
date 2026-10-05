<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmAdministratorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');

        $adminsCount = SchoolUser::where('school_id', $schoolId)->count();
        $rolesCount = StaffRole::where('school_id', $schoolId)->count();
        $staffCount = Staff::where('school_id', $schoolId)->where('status', 'Active')->count();
        $leavesCount = StaffLeave::where('school_id', $schoolId)
            ->where('status', 'Pending')->count();

        return view('sm-administrator.dashboard', compact(
            'adminsCount',
            'rolesCount',
            'staffCount',
            'leavesCount',
        ));
    }
}
