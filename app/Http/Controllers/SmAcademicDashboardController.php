<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\Medium;
use App\Models\NoticeBoard;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentLeave;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SmAcademicDashboardController extends Controller
{
    private function getSchoolAndSession(Request $request): array
    {
        $schoolId = session('selected_school_id');
        abort_if(! $schoolId, 403, 'No school selected.');
        $school = School::findOrFail($schoolId);

        $sessionId = $request->query('session_id')
            ?: session('selected_session_id')
            ?: SystemSetting::getSetting('current_session_id');

        abort_if(! $sessionId, 403, 'No academic session selected or configured.');

        session([
            'selected_session_id' => $sessionId,
            'selected_academic_session_id' => $sessionId,
        ]);

        $session = AcademicSession::findOrFail($sessionId);

        return [$school, $session];
    }

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession($request);
        $sid = $school->id;
        $sessId = $session->id;

        $sessions = AcademicSession::orderBy('start_date', 'desc')->get();
        $globalCurrentSessionId = SystemSetting::getSetting('current_session_id');
        $globalCurrentSession = AcademicSession::find($globalCurrentSessionId);

        // ── 1. Classes & Sections ────────────────────────────────────────────────
        $classes = SchoolClass::where('school_id', $sid)
            ->where('academic_session_id', $sessId)
            ->get();
        $totalClasses = $classes->count();

        $sections = Section::whereHas('schoolClass', fn ($q) => $q->where('school_id', $sid)->where('academic_session_id', $sessId))->get();
        $totalSections = $sections->count();

        // ── 2. Subjects & Subject Types Distribution ─────────────────────────────
        $subjects = Subject::with('subjectType')
            ->where('school_id', $sid)
            ->where('academic_session_id', $sessId)
            ->get();
        $totalSubjects = $subjects->count();

        $palette = ['#B47A42', '#3B82F6', '#10B981', '#8B5CF6', '#EC4899', '#F59E0B', '#06B6D4'];
        $stGrouped = $subjects->groupBy(fn ($s) => $s->subjectType?->name ?: 'Core / General');
        $subjectTypesData = [];
        $colorIdx = 0;
        foreach ($stGrouped as $typeName => $items) {
            $count = $items->count();
            $pct = $totalSubjects > 0 ? round(($count / $totalSubjects) * 100, 1) : 0;
            $subjectTypesData[] = [
                'name' => $typeName,
                'count' => $count,
                'percentage' => $pct,
                'color' => $palette[$colorIdx % count($palette)],
            ];
            $colorIdx++;
        }

        // ── 3. Class Teachers & Allocation Coverage ──────────────────────────────
        $classTeachers = ClassTeacher::with('staff')
            ->where('school_id', $sid)
            ->where('session_id', $sessId)
            ->get();
        $assignedClassIds = $classTeachers->pluck('class_id')->unique()->filter();
        $assignedClassesCount = $assignedClassIds->count();
        $unassignedClassesCount = max(0, $totalClasses - $assignedClassesCount);
        $teacherCoverageRate = $totalClasses > 0 ? round(($assignedClassesCount / $totalClasses) * 100, 1) : 0;
        $totalClassTeachers = $classTeachers->pluck('staff_id')->unique()->count();

        // ── 4. Study Materials by Format & Coverage ──────────────────────────────
        $studyMaterials = StudyMaterial::where('school_id', $sid)
            ->where('academic_session_id', $sessId)
            ->get();
        $totalMaterials = $studyMaterials->count();
        $materialTypesGrouped = $studyMaterials->groupBy(fn ($m) => ucfirst(strtolower($m->type ?: 'document')));
        $materialTypesData = [];
        $matColors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EC4899'];
        $mIdx = 0;
        foreach ($materialTypesGrouped as $matType => $items) {
            $materialTypesData[] = [
                'name' => $matType,
                'count' => $items->count(),
                'percentage' => $totalMaterials > 0 ? round(($items->count() / $totalMaterials) * 100, 1) : 0,
                'color' => $matColors[$mIdx % count($matColors)],
            ];
            $mIdx++;
        }
        $classesWithMaterialsCount = $studyMaterials->pluck('school_class_id')->unique()->count();
        $materialsCoverageRate = $totalClasses > 0 ? round(($classesWithMaterialsCount / $totalClasses) * 100, 1) : 0;

        // ── 5. Class-wise Subject Load ───────────────────────────────────────────
        $classesWithSubjects = SchoolClass::where('school_id', $sid)
            ->where('academic_session_id', $sessId)
            ->withCount('subjects')
            ->orderByDesc('subjects_count')
            ->take(6)
            ->get();

        // ── 6. Student Leaves Processing ─────────────────────────────────────────
        $studentLeavesBase = StudentLeave::where('school_id', $sid)
            ->where('session_id', $sessId);
        $approvedStudentLeaves = (clone $studentLeavesBase)->where('status', 'Approved')->count();
        $pendingStudentLeaves = (clone $studentLeavesBase)->where('status', 'Pending')->count();
        $rejectedStudentLeaves = (clone $studentLeavesBase)->where('status', 'Rejected')->count();
        $totalStudentLeaves = $approvedStudentLeaves + $pendingStudentLeaves + $rejectedStudentLeaves;
        $leaveApprovalRate = $totalStudentLeaves > 0 ? round(($approvedStudentLeaves / $totalStudentLeaves) * 100, 1) : 0;

        // ── 7. Notices & Mediums ─────────────────────────────────────────────────
        $notices = NoticeBoard::where('school_id', $sid)
            ->where('academic_session_id', $sessId)
            ->get();
        $totalNotices = $notices->count();
        $activeNotices = $notices->where('is_active', true)->count();
        $mediumsCount = Medium::where('school_id', $sid)->count();

        return view('sm-academic.dashboard', compact(
            'school',
            'session',
            'sessions',
            'globalCurrentSession',
            'totalClasses',
            'totalSections',
            'totalSubjects',
            'subjectTypesData',
            'assignedClassesCount',
            'unassignedClassesCount',
            'teacherCoverageRate',
            'totalClassTeachers',
            'totalMaterials',
            'materialTypesData',
            'classesWithMaterialsCount',
            'materialsCoverageRate',
            'classesWithSubjects',
            'approvedStudentLeaves',
            'pendingStudentLeaves',
            'rejectedStudentLeaves',
            'totalStudentLeaves',
            'leaveApprovalRate',
            'totalNotices',
            'activeNotices',
            'mediumsCount'
        ));
    }
}
