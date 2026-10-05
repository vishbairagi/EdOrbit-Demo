<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\SystemSetting;

class SmAcademicDashboardController extends Controller
{
    private function getSchoolAndSession(): array
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        abort_if(! $sessionId, 403, 'No academic session selected or configured.');
        $session = AcademicSession::findOrFail($sessionId);

        return [$school, $session];
    }

    public function index()
    {
        [$school, $session] = $this->getSchoolAndSession();

        $totalClasses = SchoolClass::where('school_id', $school->id)->where('academic_session_id', $session->id)->count();
        $totalSections = Section::whereHas('schoolClass', fn ($q) => $q->where('school_id', $school->id)->where('academic_session_id', $session->id))->count();
        $totalSubjects = Subject::where('school_id', $school->id)->where('academic_session_id', $session->id)->count();
        $totalMaterials = StudyMaterial::where('school_id', $school->id)->where('academic_session_id', $session->id)->count();

        return view('sm-academic.dashboard', compact('school', 'session', 'totalClasses', 'totalSections', 'totalSubjects', 'totalMaterials'));
    }
}
