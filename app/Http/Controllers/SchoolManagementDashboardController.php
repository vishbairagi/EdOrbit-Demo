<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;

class SchoolManagementDashboardController extends Controller
{
    public function index()
    {
        $totalSchools = School::count();
        $activeSchools = School::where('status', 'active')->count();
        $inactiveSchools = School::where('status', 'inactive')->count();
        $totalSessions = AcademicSession::count();

        $schools = School::latest()->take(5)->get();

        return view('school-management.dashboard', compact(
            'totalSchools',
            'activeSchools',
            'inactiveSchools',
            'totalSessions',
            'schools'
        ));
    }
}
