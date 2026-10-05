<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeePeriod;
use App\Models\FeeType;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTypeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_type_crud_is_school_scoped_and_admission_saves_selection(): void
    {
        [$school, $session, $schoolClass] = $this->schoolAndClass();
        $sessionData = [
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ];

        $this->withSession($sessionData)
            ->post(route('sm-school.settings.student-types.store'), ['name' => '  Day   Scholar '])
            ->assertRedirect(route('sm-school.settings.index', ['tab' => 'student-types']));

        $studentType = StudentType::where('school_id', $school->id)->firstOrFail();
        $this->withSession($sessionData)
            ->put(route('sm-school.settings.student-types.update', $studentType), ['name' => 'Day Scholar'])
            ->assertRedirect(route('sm-school.settings.index', ['tab' => 'student-types']));

        $this->withSession($sessionData)
            ->post(route('sm-student.students.store'), [
                'first_name' => 'Aarav',
                'gender' => 'Male',
                'school_class_id' => $schoolClass->id,
                'student_type_id' => $studentType->id,
            ])
            ->assertRedirect(route('sm-student.students.index'));

        $student = Student::where('school_id', $school->id)->where('first_name', 'Aarav')->firstOrFail();
        $this->assertSame($studentType->id, $student->student_type_id);
        $this->assertSame('Day Scholar', $student->studentType->name);

        $this->withSession($sessionData)
            ->delete(route('sm-school.settings.student-types.destroy', $studentType))
            ->assertSessionHas('error', 'This student type is assigned to students and cannot be deleted.');

        $this->assertDatabaseHas('student_types', ['id' => $studentType->id, 'school_id' => $school->id]);
    }

    public function test_student_cannot_be_assigned_a_type_from_another_school(): void
    {
        [$school, $session, $schoolClass] = $this->schoolAndClass();
        $otherSchool = School::create(['name' => 'South School']);
        $studentType = $otherSchool->studentTypes()->create(['name' => 'Boarding']);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->from(route('sm-student.students.create'))
            ->post(route('sm-student.students.store'), [
                'first_name' => 'Mira',
                'gender' => 'Female',
                'school_class_id' => $schoolClass->id,
                'student_type_id' => $studentType->id,
            ])
            ->assertSessionHasErrors('student_type_id');

        $this->assertDatabaseMissing('students', ['first_name' => 'Mira']);
    }

    public function test_unused_student_type_can_be_deleted(): void
    {
        $school = School::create(['name' => 'North School']);
        $studentType = $school->studentTypes()->create(['name' => 'Boarding']);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
        ])->delete(route('sm-school.settings.student-types.destroy', $studentType))
            ->assertRedirect(route('sm-school.settings.index', ['tab' => 'student-types']));

        $this->assertDatabaseMissing('student_types', ['id' => $studentType->id]);
    }

    public function test_fee_type_session_lookup_returns_periods_and_configured_student_types(): void
    {
        [$school, $session, $schoolClass] = $this->schoolAndClass();
        $period = FeePeriod::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Quarterly',
            'duration_months' => 3,
        ]);
        $studentType = $school->studentTypes()->create(['name' => 'Day Scholar']);
        $feeType = FeeType::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'fee_period_id' => $period->id,
            'name' => 'Tuition',
            'student_type' => 'custom',
            'student_type_id' => $studentType->id,
            'amount' => '1200.00',
            'invoice_on_admission' => true,
        ]);
        $feeType->classes()->attach($schoolClass->id);

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('accounting.fee-types.classes', $session))
            ->assertJsonPath('classes.0.id', $schoolClass->id)
            ->assertJsonPath('periods.0.name', 'Quarterly')
            ->assertJsonPath('student_types.0.name', 'Day Scholar');

        $this->withSession([
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('accounting.fee-types.for-class', [
            'schoolClass' => $schoolClass->id,
            'student_type_id' => $studentType->id,
        ]))
            ->assertJsonPath('0.name', 'Tuition');
    }

    /** @return array{0: School, 1: AcademicSession, 2: SchoolClass} */
    private function schoolAndClass(): array
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 1',
        ]);

        return [$school, $session, $schoolClass];
    }
}
