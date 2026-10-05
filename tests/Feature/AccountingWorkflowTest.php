<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeePeriod;
use App\Models\FeeType;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolUser;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_type_is_saved_with_multiple_classes_for_its_session(): void
    {
        [$school, $session, $schoolClass, $period] = $this->schoolSessionClassAndPeriod();
        $secondClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 2',
        ]);

        $this->withSession($this->schoolSession($school, $session))
            ->post(route('accounting.fee-types.store'), [
                'academic_session_id' => $session->id,
                'fee_period_id' => $period->id,
                'name' => 'Tuition Fee',
                'student_type' => 'all',
                'amount' => '1250.00',
                'class_ids' => [$schoolClass->id, $secondClass->id],
                'invoice_on_admission' => '1',
            ])
            ->assertRedirect(route('accounting.fee-types.index'));

        $feeType = FeeType::firstOrFail();
        $this->assertDatabaseHas('fee_type_school_class', [
            'fee_type_id' => $feeType->id,
            'school_class_id' => $schoolClass->id,
        ]);
        $this->assertDatabaseHas('fee_type_school_class', [
            'fee_type_id' => $feeType->id,
            'school_class_id' => $secondClass->id,
        ]);
        $this->assertSame('1250.00', $feeType->amount);
    }

    public function test_manual_invoice_creation_creates_one_invoice_per_selected_student(): void
    {
        [$school, $session, $schoolClass] = $this->schoolSessionClassAndPeriod();
        $firstStudent = $this->createStudent($school, $session, $schoolClass, 'Riya');
        $secondStudent = $this->createStudent($school, $session, $schoolClass, 'Kabir');

        $this->withSession($this->schoolSession($school, $session))
            ->post(route('accounting.fee-invoices.store'), [
                'school_class_id' => $schoolClass->id,
                'student_ids' => [$firstStudent->id, $secondStudent->id],
                'invoice_title' => 'Annual Examination',
                'description' => 'Annual examination fee',
                'allow_partial_payment' => '1',
                'total_amount' => '800.00',
                'due_amount' => '800.00',
                'issued_at' => '2026-09-29',
                'due_at' => '2026-10-15',
            ])
            ->assertRedirect(route('accounting.fee-invoices.index'));

        $this->assertDatabaseCount('fee_invoices', 2);
        $this->assertDatabaseHas('fee_invoices', [
            'student_id' => $firstStudent->id,
            'invoice_title' => 'Annual Examination',
            'allow_partial_payment' => true,
            'status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('fee_invoices', [
            'student_id' => $secondStudent->id,
            'total_amount' => '800.00',
        ]);
    }

    public function test_admission_creates_selected_invoice_on_admission_fees(): void
    {
        [$school, $session, $schoolClass, $period] = $this->schoolSessionClassAndPeriod();
        $feeType = FeeType::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'fee_period_id' => $period->id,
            'name' => 'Admission Fee',
            'student_type' => 'new_admission',
            'amount' => '500.00',
            'invoice_on_admission' => true,
        ]);
        $feeType->classes()->attach($schoolClass->id);

        $this->withSession($this->schoolSession($school, $session))
            ->post(route('sm-student.students.store'), [
                'first_name' => 'Anaya',
                'gender' => 'Female',
                'school_class_id' => $schoolClass->id,
                'fee_type_ids' => [$feeType->id],
            ])
            ->assertRedirect(route('sm-student.students.index'));

        $student = Student::where('school_id', $school->id)->where('first_name', 'Anaya')->firstOrFail();
        $this->assertDatabaseHas('fee_invoices', [
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'invoice_title' => 'Admission Fee',
            'total_amount' => '500.00',
        ]);
    }

    public function test_partial_payment_updates_invoice_and_keeps_payment_history(): void
    {
        [$school, $session, $schoolClass] = $this->schoolSessionClassAndPeriod();
        $student = $this->createStudent($school, $session, $schoolClass, 'Dev');
        $invoice = FeeInvoice::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'student_id' => $student->id,
            'invoice_title' => 'Tuition Fee',
            'allow_partial_payment' => true,
            'total_amount' => '1000.00',
            'amount_paid' => '0.00',
            'due_amount' => '1000.00',
            'issued_at' => '2026-09-29',
            'status' => 'unpaid',
        ]);
        $paymentMethod = PaymentMethod::create(['school_id' => $school->id, 'name' => 'UPI']);
        $admin = SchoolUser::create([
            'school_id' => $school->id,
            'name' => 'School Admin',
            'username' => 'school-admin',
            'password' => 'password',
        ]);

        $this->withSession($this->schoolSession($school, $session))
            ->post(route('accounting.fee-invoices.payments.store', $invoice), [
                'amount' => '250.00',
                'payment_method_id' => $paymentMethod->id,
                'transaction_id' => 'TXN-1001',
                'authorized_by' => 'admin:'.$admin->id,
                'paid_on' => '2026-09-29',
                'note' => 'First installment',
            ])
            ->assertRedirect(route('accounting.fee-invoices.show', $invoice));

        $this->assertDatabaseHas('fee_invoices', [
            'id' => $invoice->id,
            'amount_paid' => '250.00',
            'status' => 'partial',
        ]);
        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'transaction_id' => 'TXN-1001',
            'amount' => '250.00',
        ]);
        $this->assertSame(1, FeePayment::where('fee_invoice_id', $invoice->id)->count());
    }

    /** @return array{0: School, 1: AcademicSession, 2: SchoolClass, 3: FeePeriod} */
    private function schoolSessionClassAndPeriod(): array
    {
        $school = School::create(['name' => 'North School']);
        $session = AcademicSession::create(['name' => '2026-2027']);
        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Grade 1',
        ]);
        $period = FeePeriod::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'Annual',
            'duration_months' => 12,
        ]);

        return [$school, $session, $schoolClass, $period];
    }

    /** @return array<string, int|string> */
    private function schoolSession(School $school, AcademicSession $session): array
    {
        return [
            'auth_guard' => 'super_admin',
            'selected_school_id' => $school->id,
            'selected_session_id' => $session->id,
        ];
    }

    private function createStudent(School $school, AcademicSession $session, SchoolClass $schoolClass, string $name): Student
    {
        return Student::create([
            'admission_no' => $name.'-001',
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'school_class_id' => $schoolClass->id,
            'first_name' => $name,
            'gender' => 'Male',
        ]);
    }
}
