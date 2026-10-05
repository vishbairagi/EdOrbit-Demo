<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamPaper;
use App\Models\ExamResult;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_saves_papers_assigned_to_the_selected_class(): void
    {
        $setup = $this->createExamSetup();
        $subject = Subject::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'name' => 'Science',
            'code' => 'SCI',
            'type' => 'theory',
        ]);

        $response = $this->actingForSchool($setup)->post(route('examination.exams.store'), $this->examData($setup, $subject));

        $response->assertRedirect(route('examination.exams.index'));
        $this->assertDatabaseHas('exams', [
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'title' => 'Quarterly Examination',
            'exam_type' => 'marks',
        ]);
        $this->assertDatabaseHas('exam_papers', [
            'subject_id' => $subject->id,
            'paper_code' => 'SCI-1',
            'maximum_marks' => 100,
        ]);
        $exam = Exam::where('title', 'Quarterly Examination')->firstOrFail();
        $this->assertSame('Pass', $exam->grade_criteria[0]['status']);
    }

    public function test_exam_rejects_a_subject_from_another_class(): void
    {
        $setup = $this->createExamSetup();
        $otherClass = SchoolClass::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'name' => 'Grade 6',
        ]);
        $subject = Subject::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $otherClass->id,
            'name' => 'History',
        ]);

        $response = $this->actingForSchool($setup)
            ->from(route('examination.exams.create'))
            ->post(route('examination.exams.store'), $this->examData($setup, $subject));

        $response->assertSessionHasErrors('subject_ids.0');
        $this->assertDatabaseMissing('exams', ['title' => 'Quarterly Examination']);
    }

    public function test_active_student_without_a_section_can_generate_an_admit_card_when_subject_matches(): void
    {
        $setup = $this->createExamSetup();
        $subject = Subject::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'name' => 'Science',
            'code' => 'SCI',
            'type' => 'theory',
        ]);
        $student = Student::create([
            'admission_no' => 'ADM00001',
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'section_id' => null,
            'first_name' => 'Unsectioned Student',
            'gender' => 'Male',
            'status' => 'Active',
        ]);
        $student->subjects()->attach($subject->id, ['is_removed' => false]);
        $exam = Exam::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'title' => 'Quarterly Examination',
            'exam_center' => 'Main Campus',
            'start_date' => '2026-11-11',
            'end_date' => '2026-11-19',
            'exam_type' => 'marks',
            'grade_criteria' => [['percentage_from' => 0, 'percentage_to' => 100, 'grade' => 'A', 'status' => 'Pass']],
            'status' => 'active',
        ]);
        $exam->sections()->attach($setup['section']->id);
        ExamPaper::create([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
            'subject_name' => $subject->name,
            'subject_type' => 'Theory',
            'maximum_marks' => 100,
            'paper_code' => 'SCI-1',
            'paper_date' => '2026-11-12',
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $response = $this->actingForSchool($setup)
            ->get(route('examination.admit-cards.generate-form', $exam));

        $response->assertSee('Unsectioned Student')->assertSee('Science');

        $card = ExamAdmitCard::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'exam_roll_number' => 'CS1',
        ]);
        $this->actingForSchool($setup)
            ->get(route('examination.admit-cards.generate-form', $exam))
            ->assertDontSee('Unsectioned Student');

        $this->actingForSchool($setup)
            ->delete(route('examination.admit-cards.delete', ['exam' => $exam->id, 'admitCard' => $card->id]))
            ->assertRedirect(route('examination.admit-cards.view', $exam));
        $this->assertDatabaseMissing('exam_admit_cards', ['id' => $card->id]);

        $this->actingForSchool($setup)
            ->get(route('examination.admit-cards.generate-form', $exam))
            ->assertSee('Unsectioned Student');
    }

    public function test_generated_results_view_lists_saved_results_with_single_and_bulk_print_actions(): void
    {
        $setup = $this->createExamSetup();
        $exam = Exam::create([
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'title' => 'Quarterly Examination',
            'exam_center' => 'Main Campus',
            'start_date' => '2026-11-11',
            'end_date' => '2026-11-19',
        ]);
        $generatedStudent = Student::create([
            'admission_no' => 'ADM00001',
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'section_id' => $setup['section']->id,
            'first_name' => 'Generated Student',
        ]);
        Student::create([
            'admission_no' => 'ADM00002',
            'school_id' => $setup['school']->id,
            'academic_session_id' => $setup['session']->id,
            'school_class_id' => $setup['class']->id,
            'section_id' => $setup['section']->id,
            'first_name' => 'Pending Student',
        ]);
        ExamResult::create(['exam_id' => $exam->id, 'student_id' => $generatedStudent->id]);

        $response = $this->actingForSchool($setup)
            ->get(route('examination.results.view', $exam));

        $response->assertOk()
            ->assertSee('Generated Student')
            ->assertDontSee('Pending Student')
            ->assertSee('Print All Results')
            ->assertSee('Print result for Generated Student')
            ->assertSee('data-print-result="'.$exam->results()->firstOrFail()->id.'"', false);
    }

    private function createExamSetup(): array
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 5',
        ]);
        $section = Section::create(['school_class_id' => $class->id, 'name' => 'Section A']);

        return compact('school', 'session', 'class', 'section');
    }

    private function examData(array $setup, Subject $subject): array
    {
        return [
            'title' => 'Quarterly Examination',
            'exam_center' => 'Main Campus',
            'start_date' => '2026-11-11',
            'end_date' => '2026-11-19',
            'school_class_id' => $setup['class']->id,
            'section_ids' => [$setup['section']->id],
            'exam_type' => 'marks',
            'status' => 'active',
            'subject_ids' => [$subject->id],
            'papers' => [
                $subject->id => [
                    'subject_type' => 'theory',
                    'maximum_marks' => 100,
                    'paper_code' => 'SCI-1',
                    'paper_date' => '2026-11-12',
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'room_number' => '101',
                ],
            ],
            'grade_criteria' => [
                'percentage_from' => [0],
                'percentage_to' => [100],
                'grade' => ['A'],
                'status' => ['Pass'],
            ],
        ];
    }

    private function actingForSchool(array $setup): self
    {
        return $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $setup['school']->id,
            'selected_session_id' => $setup['session']->id,
        ]);
    }
}
