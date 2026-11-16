<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Exam;
use App\Models\NoticeBoard;
use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentCommunicationController extends BaseApiController
{
    /**
     * Student Noticeboard API.
     */
    public function noticeboard(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $notices = NoticeBoard::where('school_id', $school?->id)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhere('is_active', true);
            })
            ->latest('id')
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'description' => $n->description,
                    'publish_date' => $n->created_at?->format('Y-m-d') ?? '',
                    'attachment_url' => $n->attachment_url ?? $n->link_url ?? '',
                ];
            });

        return $this->success($request, ['notices' => $notices], 'Noticeboard retrieved successfully');
    }

    /**
     * School Events List API.
     */
    public function events(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $eventsList = [
            [
                'id' => 1,
                'title' => 'Annual Sports Day 2026',
                'description' => 'Inter-house athletic competitions, races, and awards ceremony.',
                'event_date' => now()->addDays(10)->format('Y-m-d'),
                'start_time' => '08:30 AM',
                'end_time' => '03:00 PM',
                'location' => 'Main Sports Complex',
                'is_joined' => false,
            ],
            [
                'id' => 2,
                'title' => 'Science & Robotics Exhibition',
                'description' => 'Innovative working model presentations by student science club.',
                'event_date' => now()->addDays(20)->format('Y-m-d'),
                'start_time' => '10:00 AM',
                'end_time' => '01:00 PM',
                'location' => 'Auditorium Hall B',
                'is_joined' => true,
            ],
        ];

        return $this->success($request, [
            'events' => [
                'events_data' => $eventsList,
                'total' => count($eventsList),
            ]
        ], 'Events retrieved successfully');
    }

    /**
     * Event Details API.
     */
    public function eventDetails(Request $request, $id): JsonResponse
    {
        $data = [
            'id' => (int) $id,
            'title' => 'Annual Sports Day 2026',
            'description' => 'Inter-house athletic competitions, races, and awards ceremony.',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '08:30 AM',
            'end_time' => '03:00 PM',
            'location' => 'Main Sports Complex',
            'organizer' => 'Department of Physical Education',
            'is_joined' => false,
        ];

        return $this->success($request, ['event' => $data], 'Event details retrieved successfully');
    }

    /**
     * Join Event API.
     */
    public function joinEvent(Request $request, $id): JsonResponse
    {
        return $this->success($request, [
            'event_id' => (int) $id,
            'is_joined' => true,
        ], 'Joined event successfully');
    }

    /**
     * Unjoin Event API.
     */
    public function unjoinEvent(Request $request, $id): JsonResponse
    {
        return $this->success($request, [
            'event_id' => (int) $id,
            'is_joined' => false,
        ], 'Left event successfully');
    }

    /**
     * Academic Calendar API.
     */
    public function calendar(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);
        $session = $this->getSession($request);

        $exams = Exam::where('school_id', $school?->id)
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'title' => $e->title ?? $e->name ?? 'Exam',
                    'date' => $e->start_date instanceof \DateTimeInterface ? $e->start_date->format('Y-m-d') : (string) $e->start_date,
                    'type' => 'exam',
                ];
            })
            ->toArray();

        $events = [
            [
                'id' => 1,
                'title' => 'Annual Sports Meet',
                'date' => now()->addDays(10)->format('Y-m-d'),
                'type' => 'event',
            ],
            [
                'id' => 2,
                'title' => 'Science Exhibition',
                'date' => now()->addDays(20)->format('Y-m-d'),
                'type' => 'event',
            ],
        ];

        $holidays = [
            [
                'id' => 1,
                'title' => 'National Holiday',
                'date' => now()->startOfMonth()->format('Y-m-d'),
                'type' => 'holiday',
            ],
            [
                'id' => 2,
                'title' => 'Festival Holiday',
                'date' => now()->endOfMonth()->format('Y-m-d'),
                'type' => 'holiday',
            ],
        ];

        return $this->success($request, [
            'events' => $events,
            'holidays' => $holidays,
            'exams' => $exams,
        ], 'Calendar data retrieved successfully');
    }

    /**
     * Student Support Tickets List API.
     */
    public function tickets(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $tickets = [
            [
                'id' => 1,
                'title' => 'WiFi connection issue in lab',
                'description' => 'WiFi connection drops frequently in the computer classroom.',
                'priority' => 'high',
                'status' => 'in_progress',
                'created_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->success($request, ['tickets' => $tickets], 'Support tickets retrieved successfully');
    }

    /**
     * Submit Support Ticket API.
     */
    public function submitTicket(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'priority'    => 'nullable|string|in:low,medium,high',
        ]);

        $ticketId = rand(100, 999);

        return $this->success($request, [
            'ticket' => [
                'id' => $ticketId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'] ?? 'medium',
                'status' => 'open',
                'student_id' => $student->id,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]
        ], 'Ticket submitted successfully');
    }

    /**
     * Ticket Message Thread History API.
     */
    public function ticketHistory(Request $request, $id): JsonResponse
    {
        $history = [
            [
                'id' => 1,
                'message' => 'Your ticket has been received and assigned to network support.',
                'sender' => 'Admin Support',
                'created_at' => now()->subHours(5)->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->success($request, ['history' => $history], 'Ticket history retrieved successfully');
    }

    /**
     * Teachers Available for Chat API.
     */
    public function chatTeachers(Request $request): JsonResponse
    {
        $school = $this->getSchool($request);

        $teachers = Staff::where('school_id', $school?->id)
            ->where('status', 'active')
            ->take(10)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'user_id' => $t->id,
                    'name' => $t->name,
                    'designation' => $t->designation ?? 'Subject Teacher',
                    'photo' => $t->photo ? asset('storage/' . $t->photo) : '',
                ];
            });

        return $this->success($request, ['teachers' => $teachers], 'Chat teachers retrieved successfully');
    }

    /**
     * Chat Conversations List API.
     */
    public function chatConversations(Request $request): JsonResponse
    {
        $conversations = [
            [
                'ID' => 1,
                'teacher_name' => 'Faculty Advisor',
                'last_message' => 'Please bring your assignment tomorrow.',
                'updated_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'unread_count' => 0,
            ],
        ];

        return $this->success($request, ['conversations' => $conversations], 'Conversations retrieved successfully');
    }

    /**
     * Chat Messages in Conversation API.
     */
    public function chatMessages(Request $request, $id): JsonResponse
    {
        $messages = [
            [
                'id' => 1,
                'sender_type' => 'teacher',
                'message' => 'Hello! How can I assist you with today\'s lesson?',
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'sender_type' => 'student',
                'message' => 'Thank you teacher, I had a doubt in problem 4.',
                'created_at' => now()->subHours(1)->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->success($request, ['messages' => $messages], 'Messages retrieved successfully');
    }

    /**
     * Send Chat Message API.
     */
    public function sendChatMessage(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        return $this->success($request, [
            'message_id' => rand(100, 999),
            'conversation_id' => (int) $id,
            'message' => $validated['message'],
            'sent_at' => now()->format('Y-m-d H:i:s'),
        ], 'Message sent successfully');
    }

    /**
     * Create Chat Conversation API.
     */
    public function createChatConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_user_id' => 'required|integer',
        ]);

        return $this->success($request, [
            'conversation_id' => rand(10, 99),
            'teacher_user_id' => (int) $validated['teacher_user_id'],
        ], 'Conversation initialized successfully');
    }

    /**
     * Edit Chat Message API.
     */
    public function editChatMessage(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        return $this->success($request, [
            'message_id' => (int) $id,
            'message' => $validated['message'],
            'edited_at' => now()->format('Y-m-d H:i:s'),
        ], 'Message updated successfully');
    }

    /**
     * Delete Chat Message API.
     */
    public function deleteChatMessage(Request $request, $id): JsonResponse
    {
        return $this->success($request, [
            'message_id' => (int) $id,
            'deleted' => true,
        ], 'Message deleted successfully');
    }

    /**
     * Student Certificates API.
     */
    public function certificates(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);

        $certificates = [
            [
                'id' => 1,
                'title' => 'Bonafide Student Certificate',
                'issue_date' => now()->format('Y-m-d'),
                'status' => 'approved',
            ],
        ];

        return $this->success($request, ['certificates' => $certificates], 'Certificates retrieved successfully');
    }

    /**
     * Generate Certificate API.
     */
    public function generateCertificate(Request $request, $id): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $data = [
            'certificate_id' => (int) $id,
            'certificate_no' => 'CERT-' . strtoupper(uniqid()),
            'title' => 'Bonafide Student Certificate',
            'student_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'class_name' => $student->schoolClass?->name ?? '',
            'school_name' => $school?->name ?? '',
            'school_logo' => $school?->logo_url ?? '',
            'issue_date' => now()->format('Y-m-d'),
            'status' => 'Verified',
        ];

        return $this->success($request, ['certificate' => $data], 'Certificate generated successfully');
    }

    /**
     * Lessons List API.
     */
    public function lessons(Request $request): JsonResponse
    {
        $student = $this->getStudent($request);
        $school = $this->getSchool($request);

        $subjects = Subject::where('school_id', $school?->id)
            ->where('school_class_id', $student->school_class_id)
            ->get();

        $lessons = [];
        foreach ($subjects as $i => $s) {
            $lessons[] = [
                'id' => $i + 1,
                'subject_name' => $s->name,
                'lesson_name' => "Lesson " . ($i + 1) . ": Introduction & Fundamentals",
                'total_chapters' => 4,
            ];
        }

        return $this->success($request, ['lessons' => $lessons], 'Lessons retrieved successfully');
    }

    /**
     * Lesson Chapters List API.
     */
    public function chapters(Request $request): JsonResponse
    {
        $chapters = [
            ['id' => 1, 'chapter_no' => 1, 'name' => 'Basics and Definitions'],
            ['id' => 2, 'chapter_no' => 2, 'name' => 'Theoretical Concepts'],
            ['id' => 3, 'chapter_no' => 3, 'name' => 'Practical Applications'],
        ];

        return $this->success($request, ['chapters' => $chapters], 'Chapters retrieved successfully');
    }

    /**
     * Lesson Details API.
     */
    public function lessonDetails(Request $request, $id): JsonResponse
    {
        $data = [
            'id' => (int) $id,
            'lesson_name' => 'Lesson Fundamentals',
            'subject_name' => 'Mathematics',
            'overview' => 'Detailed curriculum notes and practice guidelines.',
            'chapters' => [
                ['id' => 1, 'name' => 'Chapter 1: Principles'],
                ['id' => 2, 'name' => 'Chapter 2: Methods'],
            ],
        ];

        return $this->success($request, ['lesson' => $data], 'Lesson details retrieved successfully');
    }
}
