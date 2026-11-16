<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\LibraryBookIssue;
use App\Models\NoticeBoard;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentLeave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends BaseApiController
{
    /**
     * Student Dashboard Summary API.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $schoolId = $school?->id;
        $sessionId = $session?->id;

        // 1. Fee Summary
        $invoiceQuery = FeeInvoice::where('student_id', $student->id);
        if ($sessionId) {
            $invoiceQuery->where('academic_session_id', $sessionId);
        }
        $totalInvoiced = (float) $invoiceQuery->sum('total_amount');
        $totalPaid = (float) $invoiceQuery->sum('amount_paid');
        $totalDue = max(0, $totalInvoiced - $totalPaid);

        $currency = SchoolSetting::getSetting($schoolId, $sessionId, 'currency_symbol', '₹');

        // 2. Attendance Summary
        $attendanceStats = [
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'half_days' => 0,
            'total_working_days' => 0,
            'attendance_percentage' => '100.00%',
        ];

        // 3. Books Issued Count
        $issuedBooksCount = LibraryBookIssue::where('student_id', $student->id)
            ->where('status', 'issued')
            ->count();

        // 4. Upcoming Exams
        $examsQuery = Exam::where('school_id', $schoolId);
        if ($sessionId) {
            $examsQuery->where('academic_session_id', $sessionId);
        }
        if ($student->school_class_id) {
            $examsQuery->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                  ->orWhereNull('school_class_id');
            });
        }
        $upcomingExamsCount = $examsQuery->where('start_date', '>=', now()->toDateString())->count();

        // 5. Recent Notices
        $recentNotices = NoticeBoard::where('school_id', $schoolId)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhere('is_active', true);
            })
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'description' => $notice->description,
                    'publish_date' => $notice->created_at?->format('Y-m-d') ?? '',
                    'attachment_url' => $notice->attachment_url ?? $notice->link_url ?? '',
                ];
            });

        // 6. Recent Invoices
        $recentInvoices = FeeInvoice::where('student_id', $student->id)
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number ?? ('INV-' . $inv->id),
                    'title' => $inv->invoice_title ?? $inv->title ?? 'Tuition & Fee',
                    'total_amount' => (float) $inv->total_amount,
                    'paid_amount' => (float) ($inv->amount_paid ?? 0),
                    'due_amount' => (float) ($inv->due_amount ?? max(0, $inv->total_amount - ($inv->amount_paid ?? 0))),
                    'status' => $inv->status,
                    'due_date' => $inv->due_at instanceof \DateTimeInterface ? $inv->due_at->format('Y-m-d') : ($inv->due_at ?? ''),
                ];
            });

        $data = [
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'roll_number' => $student->roll_number,
                'class' => $student->schoolClass?->name ?? '',
                'section' => $student->section?->name ?? '',
                'photo' => $student->photo_url ?? '',
            ],
            'school' => [
                'id' => $school?->id,
                'name' => $school?->name ?? '',
                'logo' => $school?->logo_url ?? '',
                'phone' => $school?->phone ?? '',
                'address' => $school?->address ?? '',
            ],
            'fee_summary' => [
                'currency' => $currency,
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
            ],
            'attendance_summary' => $attendanceStats,
            'upcoming_exams_count' => $upcomingExamsCount,
            'issued_books_count' => $issuedBooksCount,
            'recent_notices' => $recentNotices,
            'recent_invoices' => $recentInvoices,
        ];

        return $this->success($request, $data, 'Student dashboard retrieved successfully');
    }

    /**
     * Dashboard widget configurations.
     */
    public function dashboardSettings(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $settings = [
            'show_attendance' => true,
            'show_fee_summary' => true,
            'show_exams' => true,
            'show_homework' => true,
            'show_noticeboard' => true,
            'show_library' => true,
            'show_transport' => true,
            'show_time_table' => true,
            'show_live_classes' => true,
            'currency_symbol' => SchoolSetting::getSetting($school?->id, $session?->id, 'currency_symbol', '₹'),
        ];

        return $this->success($request, $settings, 'Dashboard settings retrieved successfully');
    }

    /**
     * Fetch Push Notification Tokens.
     */
    public function getNotificationTokens(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        // Fetch tokens stored in school_settings or user meta
        $tokens = DB::table('system_settings')
            ->where('key', 'like', "student_token_{$student->id}_%")
            ->pluck('value')
            ->toArray();

        return $this->success($request, ['tokens' => $tokens], 'Notification tokens retrieved successfully');
    }

    /**
     * Save Push Notification Token.
     */
    public function saveNotificationToken(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $validated = $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        $key = "student_token_{$student->id}_" . substr(md5($validated['token']), 0, 10);
        SystemSetting::setSetting($key, $validated['token']);

        return $this->success($request, ['saved' => true], 'Notification token saved successfully');
    }
}
