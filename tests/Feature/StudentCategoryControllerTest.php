<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_category_crud_updates_the_admission_form_options(): void
    {
        $school = School::create(['name' => 'North School']);
        $category = $school->studentCategories()->create(['name' => 'General']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 1',
        ]);
        $sessionData = [
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ];

        $this->withSession($sessionData)
            ->from(route('sm-school.settings.index'))
            ->post(route('sm-school.settings.categories.store'), ['name' => '  Economically   Weaker Section  '])
            ->assertRedirect(route('sm-school.settings.index'))
            ->assertSessionHasNoErrors();

        $newCategory = StudentCategory::where('school_id', $school->id)
            ->where('name', 'Economically Weaker Section')
            ->firstOrFail();

        $this->withSession($sessionData)
            ->put(route('sm-school.settings.categories.update', $newCategory), ['name' => 'EWS'])
            ->assertRedirect(route('sm-school.settings.index'));

        $this->assertDatabaseHas('student_categories', ['school_id' => $school->id, 'name' => 'EWS']);
        $this->assertDatabaseMissing('student_categories', ['id' => $newCategory->id, 'name' => 'Economically Weaker Section']);

        $this->withSession($sessionData)
            ->get(route('sm-student.students.create'))
            ->assertSee('EWS')
            ->assertDontSee('Economically Weaker Section');

        $this->withSession($sessionData)
            ->delete(route('sm-school.settings.categories.destroy', $category))
            ->assertRedirect(route('sm-school.settings.index'));

        $this->assertDatabaseMissing('student_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('school_classes', ['id' => $schoolClass->id]);
    }

    public function test_category_names_must_be_unique_within_a_school(): void
    {
        $school = School::create(['name' => 'North School']);
        $school->studentCategories()->create(['name' => 'General']);
        $otherSchool = School::create(['name' => 'South School']);
        $otherSchool->studentCategories()->create(['name' => 'General']);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
        ])->from(route('sm-school.settings.index'))
            ->post(route('sm-school.settings.categories.store'), ['name' => 'General'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('student_categories', 2);
    }

    public function test_category_cannot_be_deleted_from_another_school(): void
    {
        $school = School::create(['name' => 'North School']);
        $otherSchool = School::create(['name' => 'South School']);
        $category = $otherSchool->studentCategories()->create(['name' => 'Private']);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
        ])->delete(route('sm-school.settings.categories.destroy', $category))
            ->assertNotFound();

        $this->assertDatabaseHas('student_categories', ['id' => $category->id, 'school_id' => $otherSchool->id]);
    }

    public function test_category_in_use_by_a_student_cannot_be_deleted(): void
    {
        $school = School::create(['name' => 'North School']);
        $category = $school->studentCategories()->create(['name' => 'General']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 1',
        ]);
        $student = Student::create([
            'admission_no' => 'ADM00001',
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $schoolClass->id,
            'first_name' => 'Aarav',
            'category' => $category->name,
        ]);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
        ])->from(route('sm-school.settings.index'))
            ->delete(route('sm-school.settings.categories.destroy', $category))
            ->assertSessionHas('error', 'This category is assigned to students and cannot be deleted.');

        $this->assertModelExists($student);
        $this->assertDatabaseHas('student_categories', ['id' => $category->id]);
    }
}