<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\RolePermission;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectClassTeacherAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_teacher_only_sees_subjects_for_assigned_class(): void
    {
        $setup = $this->createClassTeacherSetup();
        Subject::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['assignedClass']->id,
            'name' => 'Assigned Subject',
        ]);
        Subject::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['otherClass']->id,
            'name' => 'Other Class Subject',
        ]);

        $response = $this->asClassTeacher($setup)->get(route('academic.subjects.index'));

        $response->assertSee('Assigned Subject')->assertDontSee('Other Class Subject');
    }

    public function test_class_teacher_can_add_subject_to_assigned_class(): void
    {
        $setup = $this->createClassTeacherSetup();

        $response = $this->asClassTeacher($setup)->post(route('academic.subjects.store'), [
            'name' => 'New Assigned Subject',
            'school_class_id' => $setup['assignedClass']->id,
        ]);

        $response->assertRedirect(route('academic.subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['assignedClass']->id,
            'name' => 'New Assigned Subject',
        ]);
    }

    public function test_class_teacher_cannot_add_subject_to_another_class(): void
    {
        $setup = $this->createClassTeacherSetup();

        $response = $this->asClassTeacher($setup)->post(route('academic.subjects.store'), [
            'name' => 'Unauthorized Subject',
            'school_class_id' => $setup['otherClass']->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('subjects', [
            'school_class_id' => $setup['otherClass']->id,
            'name' => 'Unauthorized Subject',
        ]);
    }

    /** @return array{school: School, session: AcademicSession, assignedClass: SchoolClass, otherClass: SchoolClass, staff: Staff} */
    private function createClassTeacherSetup(): array
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2025-2026']);
        $assignedClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        $otherClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 6',
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
            'permission_key' => 'academic.subjects',
        ]);
        ClassTeacher::create([
            'school_id' => $school->id,
            'session_id' => $session->id,
            'staff_id' => $staff->id,
            'class_id' => $assignedClass->id,
        ]);

        return compact('school', 'session', 'assignedClass', 'otherClass', 'staff');
    }

    private function asClassTeacher(array $setup): self
    {
        return $this->withSession([
            'auth_guard' => 'staff',
            'auth_user_id' => $setup['staff']->id,
            'selected_school_id' => $setup['school']->id,
            'selected_session_id' => $setup['session']->id,
        ]);
    }
}
