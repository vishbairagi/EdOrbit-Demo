<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\NoticeBoard;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    /**
     * Get Student Dashboard Data
     * Requires JWT Bearer Token: Authorization: Bearer <token>
     * Fetches strictly the authenticated student's data from database.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Resolve student from request attribute or Bearer token
        $student = $request->attributes->get('student');

        if (! $student) {
            try {
                $token = JWTAuth::parseToken();
                $payload = $token->getPayload();
                $studentId = $payload->get('sub') ?? ($payload->get('user')['id'] ?? null);

                if (! $studentId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid token: Student ID not found in token claims.',
                    ], 401);
                }

                $student = Student::with(['school', 'session', 'schoolClass', 'section'])->find($studentId);

                if (! $student) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student record not found for this token.',
                    ], 404);
                }
            } catch (TokenExpiredException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please login again.',
                ], 401);
            } catch (TokenInvalidException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid.',
                ], 401);
            } catch (JWTException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization Bearer token is required.',
                ], 401);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: ' . $e->getMessage(),
                ], 401);
            }
        }

        // 2. Format Student Information strictly from database
        $school = $student->school;
        $schoolData = [
            'name' => (string) ($school?->name ?? ''),
            'phone' => (string) ($school?->phone ?? ''),
            'email' => (string) ($school?->email ?? ''),
            'address' => (string) ($school?->address ?? ''),
            'description' => (string) ($school?->description ?? '-'),
            'logo' => (string) ($school?->logo ?? ''),
        ];

        $photoUrl = $student->photo;
        if ($photoUrl && ! filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $photoUrl = asset('storage/' . ltrim($photoUrl, '/'));
        } elseif (! $photoUrl) {
            $photoUrl = '';
        }

        $fullName = preg_replace('/\s+/', ' ', trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name));
        if ($fullName === '' && $student->name) {
            $fullName = $student->name;
        }

        $studentData = [
            'id' => (string) $student->id,
            'school' => $schoolData,
            'name' => (string) $fullName,
            'enrollment_number' => (string) ($student->admission_no ?? ''),
            'session' => (string) ($student->session?->name ?? ''),
            'class' => (string) ($student->schoolClass?->name ?? ''),
            'section' => (string) ($student->section?->name ?? ''),
            'roll_number' => (string) ($student->roll_number ?? ''),
            'father_name' => (string) ($student->father_name ?? ''),
            'photo' => (string) $photoUrl,
        ];

        // 3. Attendance Summary from Database (or 0s if no attendance records exist yet)
        $attendanceData = $this->getAttendanceData($student);

        // 4. Previous Session Due from Database (Fee Invoices)
        $prevSessionDue = (int) FeeInvoice::where('student_id', $student->id)
            ->when($student->academic_session_id, function ($q) use ($student) {
                $q->where('academic_session_id', '!=', $student->academic_session_id);
            })
            ->where('status', '!=', 'paid')
            ->sum('due_amount');

        // 5. Noticeboard Data strictly from Database
        $noticeboardData = $this->getNoticeboardData($student);

        return response()->json([
            'success' => true,
            'message' => 'Student dashboard retrieved successfully.',
            'data' => [
                'student' => $studentData,
                'attendance' => $attendanceData,
                'prev_session_due' => $prevSessionDue,
                'noticeboard' => $noticeboardData,
            ],
        ], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Calculate student attendance from database if records exist.
     */
    private function getAttendanceData(Student $student): array
    {
        // Check if student attendance table exists or relation exists
        $hasAttendanceTable = \Illuminate\Support\Facades\Schema::hasTable('student_attendances');

        if ($hasAttendanceTable) {
            $records = \Illuminate\Support\Facades\DB::table('student_attendances')
                ->where('student_id', $student->id)
                ->get();

            $total = $records->count();
            if ($total > 0) {
                $present = $records->where('status', 'present')->count();
                $absent = $records->where('status', 'absent')->count();
                $late = $records->where('status', 'late')->count();
                $holiday = $records->where('status', 'holiday')->count();
                $percent = round(($present / $total) * 100, 2);

                return [
                    'total_attendance' => $total,
                    'total_present' => $present,
                    'total_absent' => $absent,
                    'total_late' => $late,
                    'total_holiday' => $holiday,
                    'percentage_value' => number_format($percent, 2, '.', ''),
                    'percentage_text' => number_format($percent, 1, '.', '') . ' %',
                ];
            }
        }

        // Default empty structure if no attendance records exist in database
        return [
            'total_attendance' => 0,
            'total_present' => 0,
            'total_absent' => 0,
            'total_late' => 0,
            'total_holiday' => 0,
            'percentage_value' => '0.00',
            'percentage_text' => '0.0 %',
        ];
    }

    /**
     * Retrieve noticeboard list strictly from database for this student's school, session, and class.
     */
    private function getNoticeboardData(Student $student): array
    {
        $newNoticeIcon = 'https://mobileapp.weblizar.in/wp-content/plugins/school-management-pro/assets/images/newicon.gif';

        $query = NoticeBoard::where('is_active', true)
            ->where('school_id', $student->school_id);

        if ($student->academic_session_id) {
            $query->where(function ($q) use ($student) {
                $q->where('academic_session_id', $student->academic_session_id)
                  ->orWhereNull('academic_session_id');
            });
        }

        if ($student->school_class_id) {
            $query->where(function ($q) use ($student) {
                $q->where('school_class_id', $student->school_class_id)
                  ->orWhereNull('school_class_id');
            });
        }

        if ($student->section_id) {
            $query->where(function ($q) use ($student) {
                $q->where('section_id', $student->section_id)
                  ->orWhereNull('section_id');
            });
        }

        $dbNotices = $query->orderByDesc('created_at')->get();

        $noticesList = [];
        foreach ($dbNotices as $notice) {
            $link = '#';
            if ($notice->link_type === 'url' && $notice->link_url) {
                $link = $notice->link_url;
            } elseif ($notice->link_type === 'attachment' && $notice->attachment_path) {
                $link = $notice->attachment_url ?: route('academic.noticeboards.download', $notice->id);
            }

            $noticesList[] = [
                'id' => (string) $notice->id,
                'title' => (string) $notice->title,
                'description' => (string) ($notice->description ?? ''),
                'link' => $link,
                'date' => $notice->created_at ? $notice->created_at->format('d-m-Y') : '',
                'is_new' => $notice->created_at ? ($notice->created_at->diffInDays(now()) <= 7) : false,
                'notice_filter_data' => null,
            ];
        }

        return [
            'new_notice_icon' => $newNoticeIcon,
            'data' => $noticesList,
        ];
    }
}
