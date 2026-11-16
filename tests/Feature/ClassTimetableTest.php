<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassTimetable;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassTimetableTest extends TestCase
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
            'name' => 'B',
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

        // Assign teacher to subject
        $subject->teachers()->attach($staff->id);

        return [$school, $session, $class, $section, $subject, $staff];
    }

    public function test_class_timetable_index_screen_renders_successfully(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.class-timetable.index'));

        $response->assertStatus(200);
        $response->assertSee('Class Timetable');
        $response->assertSee('Grade 1 Demo S1');
        $response->assertSee('View Time Table');
    }

    public function test_can_add_routine_slot_for_multiple_days(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.class-timetable.store'), [
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'days' => ['Monday', 'Tuesday', 'Wednesday'],
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => 'Library',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('class_timetables', 3);
        $this->assertDatabaseHas('class_timetables', [
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Monday',
            'room_number' => 'Library',
        ]);
    }

    public function test_show_class_timetable_view_matches_image_2_data(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => 'Library',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.class-timetable.show', [
            'schoolClass' => $class->id,
            'section' => $section->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Class Timetable: Grade 1 Demo S1 - B');
        $response->assertSee('Print Class Timetable');
        $response->assertSee('Intechno Academy Demo');
        $response->assertSee('555-9118');
        $response->assertSee('Science');
        $response->assertSee('(SCI)');
        $response->assertSee('Room No. Library');
        $response->assertSee('Demo Teacher');
    }

    public function test_can_update_routine_slot(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $slot = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => '101',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->put(route('academic.class-timetable.update', $slot), [
            'day' => 'Tuesday',
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => 'Lab-2',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_timetables', [
            'id' => $slot->id,
            'day' => 'Tuesday',
            'start_time' => '09:00',
            'room_number' => 'Lab-2',
        ]);
    }

    public function test_can_delete_single_slot(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $slot = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => '101',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->delete(route('academic.class-timetable.destroy', $slot));

        $response->assertRedirect();
        $this->assertDatabaseMissing('class_timetables', ['id' => $slot->id]);
    }

    public function test_class_data_endpoint_returns_only_assigned_teachers_for_subject(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.class-timetable.class-data', $class));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('subjects', $data);
        $this->assertCount(1, $data['subjects']);
        $this->assertEquals('Science', $data['subjects'][0]['name']);
        $this->assertCount(1, $data['subjects'][0]['teachers']);
        $this->assertEquals('Demo Teacher', $data['subjects'][0]['teachers'][0]['name']);
    }

    public function test_can_bulk_delete_class_timetables(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        $slot1 = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => '101',
        ]);

        $slot2 = ClassTimetable::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'staff_id' => $staff->id,
            'day' => 'Tuesday',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => '102',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.class-timetable.bulk-destroy'), [
            'selected_keys' => ["{$class->id}:{$section->id}"],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('class_timetables', ['id' => $slot1->id]);
        $this->assertDatabaseMissing('class_timetables', ['id' => $slot2->id]);
    }

    public function test_class_timetable_pagination_works(): void
    {
        [$school, $session, $class, $section, $subject, $staff] = $this->setupData();

        // Create 11 additional classes
        for ($i = 2; $i <= 12; $i++) {
            $newClass = SchoolClass::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'name' => "Grade $i Demo",
                'order' => $i,
            ]);
            Section::create([
                'school_class_id' => $newClass->id,
                'name' => 'A',
            ]);
        }

        $response = $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.class-timetable.index', ['per_page' => 10]));

        $response->assertStatus(200);
        $response->assertSeeText('Showing 1 to 10 of 12 class section(s)');
        $response->assertSee('page=2');
    }
}
