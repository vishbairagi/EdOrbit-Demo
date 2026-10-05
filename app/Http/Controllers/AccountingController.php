<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Staff;
use App\Models\SystemSetting;

abstract class AccountingController extends Controller
{
    protected function selectedSchool(): School
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        if (session('auth_guard') === 'school_user') {
            $user = SchoolUser::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        if (session('auth_guard') === 'staff') {
            $user = Staff::find(session('auth_user_id'));
            abort_if(! $user || (int) $user->school_id !== $school->id, 403);
        }

        return $school;
    }

    protected function selectedSession(): AcademicSession
    {
        $sessionId = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');
        abort_if(! $sessionId, 403, 'No academic session selected.');

        return AcademicSession::findOrFail($sessionId);
    }
}
