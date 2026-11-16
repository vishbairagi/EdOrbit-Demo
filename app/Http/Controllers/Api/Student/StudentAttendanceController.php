<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAttendanceController extends BaseApiController
{
    /**
     * Student Attendance Report API.
     */
    public function attendance(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $session = $this->getSession($request);

        $month = $request->input('month', now()->format('m'));
        $year = $request->input('year', now()->format('Y'));

        // Generate monthly attendance calendar view
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
        $logs = [];

        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $holidayCount = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayOfWeek = date('l', strtotime($dateStr));

            if ($dayOfWeek === 'Sunday') {
                $status = 'Holiday';
                $holidayCount++;
            } else {
                $status = 'Present';
                $presentCount++;
            }

            $logs[] = [
                'date' => $dateStr,
                'day' => $dayOfWeek,
                'status' => $status,
                'remarks' => '',
            ];
        }

        $totalWorking = $presentCount + $absentCount + $lateCount;
        $percentage = $totalWorking > 0 ? round(($presentCount / $totalWorking) * 100, 2) : 100.00;

        $data = [
            'student_id' => $student->id,
            'admission_no' => $student->admission_no,
            'month' => (int) $month,
            'year' => (int) $year,
            'summary' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'holiday' => $holidayCount,
                'total_working_days' => $totalWorking,
                'percentage' => $percentage . '%',
            ],
            'attendance_logs' => $logs,
        ];

        return $this->success($request, ['attendance' => $data], 'Attendance retrieved successfully');
    }
}
