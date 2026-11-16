<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamPaper;
use App\Models\ExamResult;
use App\Models\ExamResultItem;
use App\Models\SchoolSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentExaminationController extends BaseApiController
{
    /**
     * Exams Time Table List API.
     */
    public function examTimeTable(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolId = $school?->id;
        $sessionId = $session?->id;

        $examsQuery = Exam::with(['papers.subject'])
            ->where('school_id', $schoolId);

        if ($sessionId) {
            $examsQuery->where('academic_session_id', $sessionId);
        }

        if ($student->school_class_id) {
            $examsQuery->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                  ->orWhereNull('school_class_id');
            });
        }

        $exams = $examsQuery->latest('start_date')->get()->map(function ($exam) {
            $start = $exam->start_date instanceof \DateTimeInterface ? $exam->start_date->format('Y-m-d') : (string) $exam->start_date;
            $end = $exam->end_date instanceof \DateTimeInterface ? $exam->end_date->format('Y-m-d') : (string) $exam->end_date;

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'start_date' => $start,
                'end_date' => $end,
                'status' => $exam->status ?? 'Active',
                'papers_count' => $exam->papers->count(),
                'papers' => $exam->papers->map(function ($p) {
                    $pDate = $p->paper_date instanceof \DateTimeInterface ? $p->paper_date->format('Y-m-d') : (string) $p->paper_date;

                    return [
                        'id' => $p->id,
                        'subject_name' => $p->subject?->name ?? 'General',
                        'subject_code' => $p->subject?->code ?? '',
                        'paper_date' => $pDate,
                        'start_time' => $p->start_time ?? '09:00 AM',
                        'end_time' => $p->end_time ?? '12:00 PM',
                        'room_number' => $p->room_number ?? 'Hall 1',
                        'maximum_marks' => (float) ($p->maximum_marks ?? 100),
                        'passing_marks' => (float) ($p->passing_marks ?? 33),
                    ];
                }),
            ];
        });

        return $this->success($request, ['exam_time_table' => $exams], 'Exams time table retrieved successfully');
    }

    /**
     * Single Exam Time Table Details API.
     */
    public function examTimeTableDetails(Request $request, $id): JsonResponse
    {
        $school = $this->getSchool($request);
        $exam = Exam::with(['papers.subject'])
            ->where('school_id', $school?->id)
            ->findOrFail($id);

        $start = $exam->start_date instanceof \DateTimeInterface ? $exam->start_date->format('Y-m-d') : (string) $exam->start_date;
        $end = $exam->end_date instanceof \DateTimeInterface ? $exam->end_date->format('Y-m-d') : (string) $exam->end_date;

        $data = [
            'id' => $exam->id,
            'name' => $exam->name,
            'start_date' => $start,
            'end_date' => $end,
            'description' => $exam->description ?? '',
            'papers' => $exam->papers->map(function ($p) {
                $pDate = $p->paper_date instanceof \DateTimeInterface ? $p->paper_date->format('Y-m-d') : (string) $p->paper_date;

                return [
                    'id' => $p->id,
                    'subject_name' => $p->subject?->name ?? 'General',
                    'subject_code' => $p->subject?->code ?? '',
                    'paper_date' => $pDate,
                    'start_time' => $p->start_time ?? '09:00 AM',
                    'end_time' => $p->end_time ?? '12:00 PM',
                    'room_number' => $p->room_number ?? 'Hall 1',
                    'maximum_marks' => (float) ($p->maximum_marks ?? 100),
                    'passing_marks' => (float) ($p->passing_marks ?? 33),
                ];
            }),
        ];

        return $this->success($request, ['exam' => $data], 'Exam time table details retrieved successfully');
    }

    /**
     * Admit Cards List API.
     */
    public function admitCards(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $admitCards = ExamAdmitCard::with(['exam'])
            ->where('student_id', $student->id)
            ->get()
            ->map(function ($ac) use ($student) {
                return [
                    'id' => $ac->id,
                    'exam_id' => $ac->exam_id,
                    'exam_name' => $ac->exam?->name ?? 'Annual Examination',
                    'roll_number' => $ac->roll_number ?: ($student->roll_number ?: $student->admission_no),
                    'class_name' => $student->schoolClass?->name ?? '',
                    'section_name' => $student->section?->name ?? '',
                    'created_at' => $ac->created_at?->format('Y-m-d') ?? '',
                ];
            });

        return $this->success($request, ['admit_cards' => $admitCards], 'Admit cards retrieved successfully');
    }

    /**
     * Single Admit Card Details API.
     */
    public function admitCardDetails(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $admitCard = ExamAdmitCard::with(['exam.papers.subject'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $exam = $admitCard->exam;

        $data = [
            'id' => $admitCard->id,
            'exam_name' => $exam?->name ?? 'Final Examination',
            'roll_number' => $admitCard->roll_number ?: ($student->roll_number ?: $student->admission_no),
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'photo_url' => $student->photo_url ?? '',
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'school_phone' => $school?->phone ?? '',
            'school_address' => $school?->address ?? '',
            'exam_center' => 'Main Campus Examination Hall',
            'instructions' => '1. Bring this admit card to the examination hall.\n2. Arrive 15 minutes prior to exam start time.',
            'schedule' => $exam?->papers->map(function ($p) {
                $pDate = $p->paper_date instanceof \DateTimeInterface ? $p->paper_date->format('Y-m-d') : (string) $p->paper_date;

                return [
                    'subject' => $p->subject?->name ?? 'Subject',
                    'date' => $pDate,
                    'time' => "{$p->start_time} - {$p->end_time}",
                    'room' => $p->room_number ?? 'Hall 1',
                ];
            }) ?? [],
        ];

        return $this->success($request, ['admit_card' => $data], 'Admit card details retrieved successfully');
    }

    /**
     * Exam Results List API.
     */
    public function examResults(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $results = ExamResult::with(['exam', 'items'])
            ->where('student_id', $student->id)
            ->get()
            ->map(function ($res) {
                $totalMax = (float) $res->items->sum('maximum_marks');
                $totalObtained = (float) $res->items->sum('obtained_marks');
                $pct = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

                return [
                    'id' => $res->id,
                    'exam_id' => $res->exam_id,
                    'exam_name' => $res->exam?->name ?? 'Term Examination',
                    'total_marks' => $totalMax,
                    'obtained_marks' => $totalObtained,
                    'percentage' => $pct . '%',
                    'grade' => $pct >= 80 ? 'A+' : ($pct >= 60 ? 'A' : ($pct >= 45 ? 'B' : ($pct >= 33 ? 'C' : 'F'))),
                    'status' => $pct >= 33 ? 'Pass' : 'Fail',
                    'published_at' => $res->created_at?->format('Y-m-d') ?? '',
                ];
            });

        return $this->success($request, ['exam_results' => $results], 'Exam results retrieved successfully');
    }

    /**
     * Single Exam Result Details API.
     */
    public function examResultDetails(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $result = ExamResult::with(['exam', 'items.subject'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $totalMax = (float) $result->items->sum('maximum_marks');
        $totalObtained = (float) $result->items->sum('obtained_marks');
        $pct = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;

        $subjectBreakdown = $result->items->map(function ($item) {
            $max = (float) ($item->maximum_marks ?? 100);
            $obt = (float) $item->obtained_marks;
            $pass = (float) ($item->passing_marks ?? 33);

            return [
                'subject_name' => $item->subject?->name ?? 'General',
                'subject_code' => $item->subject?->code ?? '',
                'maximum_marks' => $max,
                'passing_marks' => $pass,
                'obtained_marks' => $obt,
                'status' => $obt >= $pass ? 'Pass' : 'Fail',
                'grade' => $obt >= 80 ? 'A+' : ($obt >= 60 ? 'A' : ($obt >= 45 ? 'B' : ($obt >= 33 ? 'C' : 'F'))),
            ];
        });

        $data = [
            'id' => $result->id,
            'exam_name' => $result->exam?->name ?? 'Term Examination',
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'roll_number' => $student->roll_number ?? '',
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'total_maximum_marks' => $totalMax,
            'total_obtained_marks' => $totalObtained,
            'percentage' => $pct . '%',
            'overall_status' => $pct >= 33 ? 'Pass' : 'Fail',
            'grade' => $pct >= 80 ? 'A+' : ($pct >= 60 ? 'A' : ($pct >= 45 ? 'B' : ($pct >= 33 ? 'C' : 'F'))),
            'subjects' => $subjectBreakdown,
        ];

        return $this->success($request, ['result' => $data], 'Exam result details retrieved successfully');
    }

    /**
     * Overall Cumulative Result API.
     */
    public function overallResult(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $results = ExamResult::with(['exam', 'items'])
            ->where('student_id', $student->id)
            ->get();

        $grandMax = 0;
        $grandObtained = 0;

        foreach ($results as $res) {
            $grandMax += (float) $res->items->sum('maximum_marks');
            $grandObtained += (float) $res->items->sum('obtained_marks');
        }

        $overallPct = $grandMax > 0 ? round(($grandObtained / $grandMax) * 100, 2) : 100.00;

        $data = [
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_name' => $student->schoolClass?->name ?? '',
            'section_name' => $student->section?->name ?? '',
            'exams_taken' => $results->count(),
            'grand_maximum_marks' => $grandMax,
            'grand_obtained_marks' => $grandObtained,
            'overall_percentage' => $overallPct . '%',
            'overall_grade' => $overallPct >= 80 ? 'A+' : ($overallPct >= 60 ? 'A' : ($overallPct >= 45 ? 'B' : ($overallPct >= 33 ? 'C' : 'F'))),
            'overall_status' => $overallPct >= 33 ? 'Pass' : 'Fail',
        ];

        return $this->success($request, ['overall_result' => $data], 'Overall cumulative result retrieved successfully');
    }
}
