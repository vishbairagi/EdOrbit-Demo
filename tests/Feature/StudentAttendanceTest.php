<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassTimetable;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $school = School::create([
            'name' => 'Intechno Academy Demo',
            'phone' => '555-9118',
            'email' => 'school1@intechno.edu.com',
            'address' => '524 124 Lakeview Road, Brookfield, New City 458201',
        ]);

        $session = AcademicSession::create(['name' => '2026-2027']);

        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 1 Demo S1',
            'order' => 1,
        ]);

        $section = Section::create([
            'school_class_id' => $class->id,
            'name' => 'A',
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'name' => 'Science',
            'code' => 'SCI',
            'type' => 'Theory',
            'is_active' => true,
        ]);

        $staff = Staff::create([
            'school_id' => $school->id,
            'name' => 'Demo Teacher',
            'username' => 'demoteacher',
            'password' => bcrypt('password'),
            'status' => 'Active',
            'email' => 'demo.teacher@school.com',
        ]);

        $student1 = Student::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_no' => 'ADM001',
            'roll_number' => 1,
            'first_name' => 'David',
            'last_name' => 'Johnson',
            'status' => 'Active',
        ]);

        $student2 = Student::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_no' => 'ADM002',
            'roll_number' => 2,
            'first_name' => 'Sarah',
            'last_name' => 'Williams',
            'status' => 'Active',
        ]);

        $dayName = Carbon::now()->format('l');

        $slot = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => $dayName,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => '101',
        ]);

        return [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot];
    }

    public function test_attendance_index_screen_renders_successfully_without_old_radio_buttons(): void
    {
        [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.attendance.index', [
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Student Attendance');
        $response->assertSee('David Johnson');
        $response->assertSee('Print Attendance Sheet');
        $response->assertDontSee('Attendance By Month');
        $response->assertDontSee('Attendance By Subject');
    }

    public function test_can_render_take_attendance_screen_with_timetable_slot(): void
    {
        [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.attendance.create', [
            'class_id' => $class->id,
            'section_id' => $section->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Take Class Attendance (Timetable-Based)');
        $response->assertSee('Science');
        $response->assertSee('David Johnson');
        $response->assertSee('Save Attendance');
    }

    public function test_can_save_attendance_for_timetable_slot(): void
    {
        [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot] = $this->setupData();

        $today = Carbon::now()->format('Y-m-d');

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.attendance.store'), [
            'date' => $today,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'class_timetable_id' => $slot->id,
            'attendances' => [
                [
                    'student_id' => $student1->id,
                    'status' => 'Present',
                    'note' => 'On time',
                ],
                [
                    'student_id' => $student2->id,
                    'status' => 'Absent',
                    'note' => 'Fever',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student1->id,
            'class_timetable_id' => $slot->id,
            'status' => 'Present',
        ]);
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student2->id,
            'class_timetable_id' => $slot->id,
            'status' => 'Absent',
            'note' => 'Fever',
        ]);
        $this->assertEquals($today, StudentAttendance::where('student_id', $student1->id)->first()->date->toDateString());
    }

    public function test_can_render_attendance_report(): void
    {
        [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.attendance.report', [
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Student Attendance Report');
        $response->assertSee('David Johnson');
    }

    public function test_can_bulk_save_attendance_for_all_timetable_slots_of_day(): void
    {
        [$school, $session, $class, $section, $subject, $staff, $student1, $student2, $slot1] = $this->setupData();

        $dayName = Carbon::now()->format('l');

        // Create a 2nd slot for this class on the same day
        $slot2 = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => $dayName,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room_number' => '102',
        ]);

        $today = Carbon::now()->format('Y-m-d');

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.attendance.store'), [
            'date' => $today,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'class_timetable_id' => 'all',
            'attendances' => [
                [
                    'student_id' => $student1->id,
                    'status' => 'Present',
                    'note' => 'Present all day',
                ],
                [
                    'student_id' => $student2->id,
                    'status' => 'Late',
                    'note' => 'Late traffic',
                ],
            ],
        ]);

        $response->assertRedirect();
        
        // Assert student1 was marked Present for BOTH slot1 and slot2
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student1->id,
            'class_timetable_id' => $slot1->id,
            'status' => 'Present',
        ]);
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student1->id,
            'class_timetable_id' => $slot2->id,
            'status' => 'Present',
        ]);

        // Assert student2 was marked Late for BOTH slot1 and slot2
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student2->id,
            'class_timetable_id' => $slot1->id,
            'status' => 'Late',
        ]);
        $this->assertDatabaseHas('student_attendances', [
            'school_id' => $school->id,
            'student_id' => $student2->id,
            'class_timetable_id' => $slot2->id,
            'status' => 'Late',
        ]);
    }
}

