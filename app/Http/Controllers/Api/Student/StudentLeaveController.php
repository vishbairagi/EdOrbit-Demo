<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\StudentLeave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentLeaveController extends BaseApiController
{
    /**
     * List Student Leave Requests API.
     */
    public function leaveRequests(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $leaves = StudentLeave::where('student_id', $student->id)
            ->latest('id')
            ->get()
            ->map(function ($leave) {
                $start = $leave->start_date instanceof \DateTimeInterface ? $leave->start_date->format('Y-m-d') : (string) $leave->start_date;
                $end = $leave->end_date instanceof \DateTimeInterface ? $leave->end_date->format('Y-m-d') : (string) $leave->end_date;

                return [
                    'id' => $leave->id,
                    'leave_type' => $leave->leave_type ?? 'Sick Leave',
                    'reason' => $leave->reason,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => $leave->status ?? 'pending',
                    'attachment' => $leave->attachment ? asset('storage/' . $leave->attachment) : '',
                    'applied_on' => $leave->created_at?->format('Y-m-d H:i:s') ?? '',
                ];
            });

        return $this->success($request, ['leave_requests' => $leaves], 'Leave requests retrieved successfully');
    }

    /**
     * Submit Student Leave Request API.
     */
    public function submitLeaveRequest(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $validated = $request->validate([
            'reason'           => 'required|string|max:500',
            'start_date'       => 'required|string',
            'end_date'         => 'nullable|string',
            'is_multiple_days' => 'nullable',
            'leave_type'       => 'nullable|string|max:50',
            'attachment'       => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        $startDate = date('Y-m-d', strtotime($validated['start_date']));
        $endDate = !empty($validated['end_date']) ? date('Y-m-d', strtotime($validated['end_date'])) : $startDate;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('student_leaves', 'public');
        }

        $leave = StudentLeave::create([
            'school_id' => $school?->id,
            'session_id' => $session?->id,
            'student_id' => $student->id,
            'class_id' => $student->school_class_id,
            'section_id' => $student->section_id,
            'leave_type' => $validated['leave_type'] ?? 'Personal Leave',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return $this->success($request, [
            'leave_id' => $leave->id,
            'status' => 'pending',
            'reason' => $leave->reason,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], 'Leave request submitted successfully');
    }
}
