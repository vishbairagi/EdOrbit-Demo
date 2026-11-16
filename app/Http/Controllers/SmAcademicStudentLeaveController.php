<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmAcademicStudentLeaveController extends Controller
{
    protected function getSchoolAndSession()
    {
        $schoolId = session('selected_school_id');
        $sessionId = session('selected_session_id') ?: \App\Models\SystemSetting::getSetting('current_session_id');
        
        $school = \App\Models\School::findOrFail($schoolId);
        $session = \App\Models\AcademicSession::findOrFail($sessionId);
        
        return [$school, $session];
    }

    public function index(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $classes = \App\Models\SchoolClass::with('sections')
            ->where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // ── Session-Wide Analytics ──
        $allSessionLeaves = \App\Models\StudentLeave::with(['student', 'schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->get();

        $allSessionStudents = \App\Models\Student::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('status', 'active')
            ->get();

        $totalLeavesCount = $allSessionLeaves->count();
        $approvedLeavesCount = $allSessionLeaves->where('status', 'Approved')->count();
        $pendingLeavesCount = $allSessionLeaves->where('status', 'Pending')->count();
        $rejectedLeavesCount = $allSessionLeaves->where('status', 'Rejected')->count();
        $approvalRate = $totalLeavesCount > 0 ? round(($approvedLeavesCount / $totalLeavesCount) * 100) : 0;

        $totalStudentsCount = $allSessionStudents->count();
        $studentsWithLeavesIds = $allSessionLeaves->pluck('student_id')->filter()->unique();
        $studentsWithLeavesCount = $studentsWithLeavesIds->count();
        $zeroLeaveCount = max(0, $totalStudentsCount - $studentsWithLeavesCount);
        $zeroLeaveRate = $totalStudentsCount > 0 ? round(($zeroLeaveCount / $totalStudentsCount) * 100) : 0;

        $totalClassesCount = $classes->count();
        $avgLeavesPerClass = $totalClassesCount > 0 ? round($totalLeavesCount / $totalClassesCount, 1) : 0;

        $currentMonth = date('Y-m');
        $currentMonthLeaves = $allSessionLeaves->filter(function($l) use ($currentMonth) {
            return $l->start_date && $l->start_date->format('Y-m') === $currentMonth;
        })->count();

        $totalLeaveDays = $allSessionLeaves->sum(function($l) {
            if (!$l->start_date || !$l->end_date) return 1;
            return max(1, $l->start_date->diffInDays($l->end_date) + 1);
        });

        // 1. Student-wise breakdown (Leaderboard of students having leaves)
        $leavesByStudent = $allSessionLeaves->groupBy('student_id');
        $studentBreakdown = $leavesByStudent->map(function ($sLeaves, $studentId) {
            $first = $sLeaves->first();
            $student = $first?->student;
            $approved = $sLeaves->where('status', 'Approved')->count();
            $pending = $sLeaves->where('status', 'Pending')->count();
            $rejected = $sLeaves->where('status', 'Rejected')->count();
            $days = $sLeaves->sum(function($l) {
                if (!$l->start_date || !$l->end_date) return 1;
                return max(1, $l->start_date->diffInDays($l->end_date) + 1);
            });

            return [
                'student_id' => $studentId,
                'name' => $student ? ($student->first_name . ' ' . ($student->last_name ?? '')) : 'Unknown Student',
                'admission_no' => $student?->admission_no ?? '—',
                'class_name' => $first?->schoolClass?->name ?? '—',
                'section_name' => $first?->section?->name ?? '—',
                'total_leaves' => $sLeaves->count(),
                'total_days' => $days,
                'approved_count' => $approved,
                'pending_count' => $pending,
                'rejected_count' => $rejected,
                'recent_leave' => $sLeaves->sortByDesc('start_date')->first(),
            ];
        })->sortByDesc('total_leaves')->values();

        // 2. Class-wise leaves breakdown
        $classBreakdown = $classes->map(function ($cls) use ($allSessionLeaves, $allSessionStudents) {
            $clsLeaves = $allSessionLeaves->where('class_id', $cls->id);
            $clsStudents = $allSessionStudents->where('school_class_id', $cls->id);
            $clsTotalStudents = $clsStudents->count();
            $clsStudentsWithLeaves = $clsLeaves->pluck('student_id')->filter()->unique()->count();
            $clsZeroLeave = max(0, $clsTotalStudents - $clsStudentsWithLeaves);
            $clsDays = $clsLeaves->sum(function($l) {
                if (!$l->start_date || !$l->end_date) return 1;
                return max(1, $l->start_date->diffInDays($l->end_date) + 1);
            });

            return [
                'class_id' => $cls->id,
                'name' => $cls->name,
                'total_students' => $clsTotalStudents,
                'zero_leave_students' => $clsZeroLeave,
                'total_leaves' => $clsLeaves->count(),
                'total_days' => $clsDays,
                'approved_count' => $clsLeaves->where('status', 'Approved')->count(),
                'pending_count' => $clsLeaves->where('status', 'Pending')->count(),
                'avg_per_student' => $clsTotalStudents > 0 ? round($clsLeaves->count() / $clsTotalStudents, 1) : 0,
            ];
        });

        // 3. Month-wise leaves breakdown
        $monthBreakdown = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthNum = str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthName = date('M', mktime(0, 0, 0, $m, 1));
            $count = $allSessionLeaves->filter(function($l) use ($monthNum) {
                return $l->start_date && $l->start_date->format('m') === $monthNum;
            })->count();
            $monthBreakdown[] = [
                'month_num' => $monthNum,
                'month_name' => $monthName,
                'count' => $count,
                'percentage' => $totalLeavesCount > 0 ? round(($count / $totalLeavesCount) * 100) : 0,
            ];
        }

        // 4. Leave Types breakdown
        $typeBreakdown = $allSessionLeaves->groupBy('leave_type')->map(function ($items, $type) use ($totalLeavesCount) {
            return [
                'type' => $type ?: 'General Leave',
                'count' => $items->count(),
                'percentage' => $totalLeavesCount > 0 ? round(($items->count() / $totalLeavesCount) * 100) : 0,
                'approved' => $items->where('status', 'Approved')->count(),
                'pending' => $items->where('status', 'Pending')->count(),
            ];
        })->values();

        $analytics = [
            'totalLeaves' => $totalLeavesCount,
            'approvedCount' => $approvedLeavesCount,
            'pendingCount' => $pendingLeavesCount,
            'rejectedCount' => $rejectedLeavesCount,
            'approvalRate' => $approvalRate,
            'totalStudents' => $totalStudentsCount,
            'studentsWithLeavesCount' => $studentsWithLeavesCount,
            'zeroLeaveCount' => $zeroLeaveCount,
            'zeroLeaveRate' => $zeroLeaveRate,
            'avgLeavesPerClass' => $avgLeavesPerClass,
            'currentMonthLeaves' => $currentMonthLeaves,
            'totalLeaveDays' => $totalLeaveDays,
            'studentBreakdown' => $studentBreakdown,
            'classBreakdown' => $classBreakdown,
            'monthBreakdown' => $monthBreakdown,
            'typeBreakdown' => $typeBreakdown,
        ];

        // ── Main Query with Filters & Pagination ──
        $query = \App\Models\StudentLeave::with(['student', 'schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->where(function($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $leaves = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        return view('sm-academic.student-leaves.index', compact('leaves', 'classes', 'school', 'session', 'analytics', 'perPage'));
    }

    public function create()
    {
        [$school, $session] = $this->getSchoolAndSession();
        $classes = \App\Models\SchoolClass::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->get();

        return view('sm-academic.student-leaves.create', compact('classes'));
    }

    public function getStudents(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        $query = \App\Models\Student::where('school_id', $school->id)
            ->where('academic_session_id', $session->id)
            ->where('school_class_id', $request->class_id);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $students = $query->get(['id', 'first_name', 'last_name']);

        return response()->json($students);
    }

    public function store(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        // Auto-fill section_id from student if not provided in the form
        if (!$request->filled('section_id') && $request->filled('student_id')) {
            $student = \App\Models\Student::find($request->student_id);
            if ($student) {
                $request->merge(['section_id' => $student->section_id]);
            }
        }

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'student_id' => 'required|exists:students,id',
            'leave_type' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|extensions:jpeg,png,jpg,pdf,doc,docx|max:2048',
            'status' => 'required|in:Approved,Pending,Rejected'
        ]);

        $data = $request->all();
        $data['school_id'] = $school->id;
        $data['session_id'] = $session->id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_leaves'), $filename);
            $data['attachment'] = 'uploads/student_leaves/' . $filename;
        }

        $leave = \App\Models\StudentLeave::create($data);

        if (\App\Models\SchoolSetting::getSetting($school->id, $session->id, 'leave_application_email_enable') == '1') {
            $student = $leave->student;
            if ($student && $student->email) {
                $carrier = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'email_carrier');
                if ($carrier === 'smtp') {
                    config(['mail.default' => 'smtp']);
                    config(['mail.mailers.smtp.host' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_host')]);
                    config(['mail.mailers.smtp.port' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_port')]);
                    config(['mail.mailers.smtp.encryption' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_encryption')]);
                    config(['mail.mailers.smtp.username' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                    config(['mail.mailers.smtp.password' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_password')]);
                    config(['mail.from.address' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'smtp_username')]);
                    config(['mail.from.name' => \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'mail_from_name')]);
                    app('mail.manager')->purge('smtp');
                    
                    $subject = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'leave_application_email_subject', 'Leave Application');
                    $rawBody = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'leave_application_email_body', 'Leave application received for [STUDENT_NAME].');
                    
                    $replacements = [
                        '[STUDENT_NAME]' => $student->full_name,
                        '[CLASS]' => $student->schoolClass->name ?? '',
                        '[SECTION]' => $student->section->name ?? '',
                        '[ROLL_NUMBER]' => $student->roll_no ?? '',
                        '[ENROLLMENT_NUMBER]' => $student->enrollment_no ?? '',
                        '[ADMISSION_NUMBER]' => $student->admission_no ?? '',
                        '[SCHOOL_NAME]' => $school->name ?? '',
                    ];
                    $body = str_replace(array_keys($replacements), array_values($replacements), $rawBody);
                    
                    try {
                        \Illuminate\Support\Facades\Mail::mailer('smtp')->html(nl2br(e($body)), function ($message) use ($student, $subject) {
                            $message->to($student->email)->subject($subject);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Leave application email failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('academic.student-leaves.index')->with('success', 'Student leave added successfully.');
    }

    public function update(Request $request, \App\Models\StudentLeave $studentLeave)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($studentLeave->school_id !== $school->id, 403);

        $request->validate([
            'leave_type' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|extensions:jpeg,png,jpg,pdf,doc,docx|max:2048',
            'status' => 'required|in:Approved,Pending,Rejected'
        ]);

        $data = $request->only(['leave_type', 'start_date', 'end_date', 'reason', 'status']);

        if ($request->hasFile('attachment')) {
            if ($studentLeave->attachment && file_exists(public_path($studentLeave->attachment))) {
                unlink(public_path($studentLeave->attachment));
            }
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_leaves'), $filename);
            $data['attachment'] = 'uploads/student_leaves/' . $filename;
        }

        $studentLeave->update($data);

        return back()->with('success', 'Student leave updated successfully.');
    }

    public function destroy(\App\Models\StudentLeave $studentLeave)
    {
        [$school, $session] = $this->getSchoolAndSession();
        abort_if($studentLeave->school_id !== $school->id, 403);
        
        if ($studentLeave->attachment && file_exists(public_path($studentLeave->attachment))) {
            unlink(public_path($studentLeave->attachment));
        }

        $studentLeave->delete();
        return back()->with('success', 'Student leave deleted successfully.');
    }

    public function export(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();

        $query = \App\Models\StudentLeave::with(['student', 'schoolClass', 'section'])
            ->where('school_id', $school->id)
            ->where('session_id', $session->id);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->where('start_date', '<=', $request->date)
                  ->where('end_date', '>=', $request->date);
        }

        $leaves = $query->latest()->get();

        $filename = "student_leaves_{$session->name}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Admission No', 'Student Name', 'Class', 'Section', 'Leave Type', 'Start Date', 'End Date', 'Reason', 'Status'];

        $callback = function() use ($leaves, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($leaves as $l) {
                fputcsv($file, [
                    $l->student?->admission_no,
                    $l->student?->first_name . ' ' . $l->student?->last_name,
                    $l->schoolClass?->name,
                    $l->section?->name,
                    $l->leave_type,
                    $l->start_date->format('Y-m-d'),
                    $l->end_date->format('Y-m-d'),
                    $l->reason,
                    $l->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=student_leaves_demo.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = ['Admission No', 'Student Name', 'Class', 'Section', 'Leave Type', 'Start Date', 'End Date', 'Reason', 'Status'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['AD-123', 'John Doe', 'Class 1', 'A', 'Sick', '2026-10-02', '2026-10-04', 'Fever', 'Pending']);
            fputcsv($file, ['AD-456', 'Jane Smith', 'Class 2', 'B', 'Personal', '2026-10-05', '2026-10-05', 'Family function', 'Approved']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|extensions:csv,txt|max:2048'
        ]);

        [$school, $session] = $this->getSchoolAndSession();

        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);

        // Map header to indices
        $map = [];
        foreach ($header as $i => $col) {
            $colName = strtolower(preg_replace('/[^a-zA-Z]/', '', $col));
            if (str_contains($colName, 'admission')) $map['admission'] = $i;
            elseif (str_contains($colName, 'leavetype')) $map['type'] = $i;
            elseif (str_contains($colName, 'startdate')) $map['start'] = $i;
            elseif (str_contains($colName, 'enddate')) $map['end'] = $i;
            elseif (str_contains($colName, 'reason')) $map['reason'] = $i;
            elseif (str_contains($colName, 'status')) $map['status'] = $i;
        }

        $errors = [];
        $importedCount = 0;

        if (!isset($map['admission']) || !isset($map['start']) || !isset($map['end']) || !isset($map['status'])) {
            return back()->with('import_errors', ["CSV is missing required headers (Admission No, Start Date, End Date, Status)."]);
        }

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            
            // Allow empty rows at the end of the file
            if (count(array_filter($row)) === 0) continue;

            $admissionNo = isset($map['admission'], $row[$map['admission']]) ? trim($row[$map['admission']]) : '';
            $leaveType = isset($map['type'], $row[$map['type']]) ? trim($row[$map['type']]) : '';
            $startDate = isset($map['start'], $row[$map['start']]) ? trim($row[$map['start']]) : '';
            $endDate = isset($map['end'], $row[$map['end']]) ? trim($row[$map['end']]) : '';
            $reason = isset($map['reason'], $row[$map['reason']]) ? trim($row[$map['reason']]) : '';
            $status = isset($map['status'], $row[$map['status']]) ? trim($row[$map['status']]) : '';

            if (empty($admissionNo) || empty($startDate) || empty($endDate) || empty($status)) {
                $errors[] = "Row $rowNum is missing required fields (Admission No, Start Date, End Date, Status).";
                continue;
            }

            // Normalize status
            $status = ucfirst(strtolower($status));
            if (!in_array($status, ['Approved', 'Pending', 'Rejected'])) {
                $errors[] = "Row $rowNum has invalid status ($status). Must be Approved, Pending, or Rejected.";
                continue;
            }

            $student = \App\Models\Student::where('school_id', $school->id)
                ->where('admission_no', $admissionNo)
                ->first();

            if (!$student) {
                $errors[] = "Row $rowNum: Student with Admission No '$admissionNo' not found.";
                continue;
            }
            
            $classId = $student->school_class_id;
            $sectionId = $student->section_id;
            
            if (!$classId) {
                $errors[] = "Row $rowNum: Student '$admissionNo' has no assigned class.";
                continue;
            }

            \App\Models\StudentLeave::create([
                'school_id' => $school->id,
                'session_id' => $session->id,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'student_id' => $student->id,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reason,
                'status' => $status
            ]);

            $importedCount++;
        }

        if (count($errors) > 0) {
            return back()->with('success', "$importedCount leaves imported successfully.")->with('import_errors', $errors);
        }

        return back()->with('success', "$importedCount leaves imported successfully.");
    }
    public function bulkDestroy(Request $request)
    {
        [$school, $session] = $this->getSchoolAndSession();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\StudentLeave::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('success', "$deleted student leave record(s) deleted successfully.");
    }
}