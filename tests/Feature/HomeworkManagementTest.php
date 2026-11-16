<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Homework;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeworkManagementTest extends TestCase
{
    use RefreshDatabase;

    private function setupSchoolAndSession(): array
    {
        $school = School::create(['name' => 'Springfield High']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => '10th Grade',
        ]);
        $section = Section::create([
            'school_class_id' => $class->id,
            'name' => 'Section A',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'type' => 'Theory',
            'is_active' => true,
        ]);

        return [$school, $session, $class, $section, $subject];
    }

    public function test_homework_index_screen_renders_successfully(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.homework.index'));

        $response->assertStatus(200);
        $response->assertSee('Homework');
        $response->assertSee('Create Homework');
        $response->assertSee('10th Grade');
        $response->assertSee('Export CSV');
    }

    public function test_can_create_homework_with_attachments_and_data_persists(): void
    {
        Storage::fake('public');
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $dummyPdf = UploadedFile::fake()->create('algebra_assignment.pdf', 120, 'application/pdf');
        $dummyImg = UploadedFile::fake()->create('diagram.png', 150, 'image/png');

        $payload = [
            'title' => 'Quadratic Equations Exercise 4.2',
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'description' => 'Complete all 10 problems on page 78.',
            'attachment_url' => 'https://example.com/math-video-tutorial',
            'attachments' => [$dummyPdf, $dummyImg],
        ];

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.homework.store'), $payload);

        $response->assertRedirect(route('academic.homework.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('homeworks', [
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'title' => 'Quadratic Equations Exercise 4.2',
            'attachment_url' => 'https://example.com/math-video-tutorial',
        ]);

        $hw = Homework::first();
        $this->assertNotNull($hw);
        $this->assertEquals(2, $hw->attachments_count);
        $this->assertNotEmpty($hw->attachments_list[0]['path']);
    }

    public function test_homework_data_is_isolated_by_school_and_session(): void
    {
        [$schoolA, $sessionA, $classA, $sectionA, $subjectA] = $this->setupSchoolAndSession();

        $schoolB = School::create(['name' => 'Shelbyville High']);
        $sessionB = AcademicSession::create(['name' => '2027-2028']);
        $classB = SchoolClass::create([
            'school_id' => $schoolB->id,
            'academic_session_id' => $sessionB->id,
            'name' => '12th Grade',
        ]);
        $subjectB = Subject::create([
            'school_id' => $schoolB->id,
            'academic_session_id' => $sessionB->id,
            'school_class_id' => $classB->id,
            'name' => 'Physics',
            'is_active' => true,
        ]);

        Homework::create([
            'school_id' => $schoolA->id,
            'academic_session_id' => $sessionA->id,
            'school_class_id' => $classA->id,
            'section_id' => $sectionA->id,
            'subject_id' => $subjectA->id,
            'title' => 'School A Assignment',
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'added_by' => 'Teacher A',
            'status' => 'active',
        ]);

        Homework::create([
            'school_id' => $schoolB->id,
            'academic_session_id' => $sessionB->id,
            'school_class_id' => $classB->id,
            'subject_id' => $subjectB->id,
            'title' => 'School B Secret Homework',
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'added_by' => 'Teacher B',
            'status' => 'active',
        ]);

        // Access as School A
        $responseA = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $schoolA->id,
            'selected_session_id' => $sessionA->id,
        ])->get(route('academic.homework.index'));

        $responseA->assertStatus(200);
        $responseA->assertSee('School A Assignment');
        $responseA->assertDontSee('School B Secret Homework');
    }

    public function test_can_update_homework(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $hw = Homework::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'title' => 'Initial Title',
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'added_by' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->put(route('academic.homework.update', $hw), [
            'title' => 'Updated Homework Title',
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'description' => 'Updated instructions',
        ]);

        $response->assertRedirect(route('academic.homework.index'));
        $this->assertDatabaseHas('homeworks', [
            'id' => $hw->id,
            'title' => 'Updated Homework Title',
            'description' => 'Updated instructions',
        ]);
    }

    public function test_can_delete_homework(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $hw = Homework::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'title' => 'To Be Deleted',
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'added_by' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->delete(route('academic.homework.destroy', $hw));

        $response->assertRedirect(route('academic.homework.index'));
        $this->assertDatabaseMissing('homeworks', ['id' => $hw->id]);
    }

    public function test_can_create_homework_with_save_and_next(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $payload = [
            'title' => 'Chapter 1 Exercise',
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'description' => 'First assignment',
            'action' => 'save_and_next',
        ];

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.homework.store'), $payload);

        $response->assertRedirect();
        $this->assertStringContainsString('prefill_class_id=' . $class->id, $response->headers->get('Location'));
        $this->assertStringContainsString('prefill_subject_id=' . $subject->id, $response->headers->get('Location'));

        $this->assertDatabaseHas('homeworks', [
            'title' => 'Chapter 1 Exercise',
            'school_id' => $school->id,
        ]);
    }

    public function test_sample_csv_download_returns_csv_file(): void
    {
        [$school, $session] = $this->setupSchoolAndSession();

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.homework.sample-csv'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_print_homework_view_renders_correctly(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        Homework::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'title' => 'Printable Homework Test',
            'homework_date' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'added_by' => 'Teacher',
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->get(route('academic.homework.print'));

        $response->assertStatus(200);
        $response->assertSee('Academic Homework Report');
        $response->assertSee('Printable Homework Test');
    }

    public function test_can_import_homework_from_csv(): void
    {
        [$school, $session, $class, $section, $subject] = $this->setupSchoolAndSession();

        $csvContent = "title,class,section,subject,homework_date,due_date,description,attachment_url\n";
        $csvContent .= "\"Imported Algebra 101\",\"{$class->name}\",\"{$section->name}\",\"{$subject->name}\",\"2026-10-22\",\"2026-10-26\",\"Solve page 50 questions\",\"\"\n";

        $file = UploadedFile::fake()->createWithContent('homework_import.csv', $csvContent);

        $response = $this->withSession([
            'auth_guard' => 'school_user',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ])->post(route('academic.homework.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('academic.homework.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('homeworks', [
            'title' => 'Imported Algebra 101',
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
        ]);
    }
}
