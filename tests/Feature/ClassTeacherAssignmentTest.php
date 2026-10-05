<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\RolePermission;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\StaffRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassTeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_classes_and_sections_are_shown_for_the_selected_session(): void
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2025-2026']);
        $otherSession = AcademicSession::create(['name' => '2026-2027']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $otherSession->id,
            'name' => 'Grade 6',
        ]);
        Section::create(['school_class_id' => $class->id, 'name' => 'Section A']);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.class-teachers.index'));

        $response->assertSee('Grade 5')->assertSee('Section A')->assertDontSee('Grade 6');
    }

    public function test_assignment_saves_the_selected_section_for_the_class(): void
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2025-2026']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        $section = Section::create(['school_class_id' => $class->id, 'name' => 'Section A']);
        $staff = Staff::create([
            'school_id' => $school->id,
            'name' => 'Demo Teacher',
            'username' => 'demo-teacher',
            'password' => 'password',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.class-teachers.store'), [
            'staff_id' => $staff->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('class_teachers', [
            'school_id' => $school->id,
            'session_id' => $session->id,
            'staff_id' => $staff->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);
    }

    public function test_assignment_rejects_a_section_from_a_different_class(): void
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2025-2026']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        $otherClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 6',
        ]);
        $section = Section::create(['school_class_id' => $otherClass->id, 'name' => 'Section B']);
        $staff = Staff::create([
            'school_id' => $school->id,
            'name' => 'Demo Teacher',
            'username' => 'demo-teacher',
            'password' => 'password',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->from(route('academic.class-teachers.index'))->post(route('academic.class-teachers.store'), [
            'staff_id' => $staff->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $response->assertSessionHasErrors('section_id');
        $this->assertDatabaseMissing('class_teachers', [
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);
    }

    public function test_assigned_class_teacher_cannot_open_class_teacher_management_with_explicit_permission(): void
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2025-2026']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        $role = StaffRole::create(['school_id' => $school->id, 'name' => 'Teacher']);
        $staff = Staff::create([
            'school_id' => $school->id,
            'staff_role_id' => $role->id,
            'name' => 'Demo Teacher',
            'username' => 'demo-teacher',
            'password' => 'password',
        ]);
        RolePermission::create([
            'staff_role_id' => $role->id,
            'permission_key' => 'academic.class-teachers',
        ]);
        ClassTeacher::create([
            'school_id' => $school->id,
            'session_id' => $session->id,
            'staff_id' => $staff->id,
            'class_id' => $class->id,
        ]);

        $response = $this->withSession([
            'auth_guard' => 'staff',
            'auth_user_id' => $staff->id,
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->from(route('academic.class-teachers.index'))->get(route('academic.class-teachers.index'));

        $response->assertRedirect(route('staff.dashboard'));
    }
}
