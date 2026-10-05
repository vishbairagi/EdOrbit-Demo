<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{School, AcademicSession, ClassGroup, SchoolClass, Section, StaffRole, Staff, SchoolUser, SubjectType, Subject, Student, ExamGroup, Exam, FeePeriod, FeeType, Vehicle, TransportRoute, Medium, ClassTeacher, StudentLeave, StudyMaterial, StaffLeave, FeeInvoice, TransportInvoice};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Cleanup old demo data before generating new one
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $demoSchools = School::where('name', 'Like', '%Artemyra Academy Demo%')->get();
        foreach($demoSchools as $ds) {
            $ds->delete(); // This cascades and deletes most things
        }
        
        // Also cleanup orphaned sessions
        AcademicSession::where('name', 'Like', '202%')->delete();
        
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. School
        $school = School::create([
            'name' => 'Artemyra Academy Demo',
            'email' => 'info@artemyrademo.edu',
            'phone' => '1234567890',
            'address' => '123 Demo Street, Education City',
            'status' => 1
        ]);

        // 2. Academic Sessions (Create 2, use 1)
        $session1 = AcademicSession::create([
            'name' => '2025-2026',
            'start_date' => Carbon::parse('2025-04-01'),
            'end_date' => Carbon::parse('2026-03-31'),
            'status' => 'inactive'
        ]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => Carbon::parse('2026-04-01'),
            'end_date' => Carbon::parse('2027-03-31'),
            'status' => 'active'
        ]);

        // 3. Class Groups (5)
        $groups = [];
        $groupNames = ['Pre-Primary', 'Primary', 'Middle', 'High', 'Higher Secondary'];
        foreach ($groupNames as $gName) {
            $groups[] = ClassGroup::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'name' => $gName,
                'status' => 1
            ]);
        }

        // 4. Classes (25) & Sections (at least A, B per class)
        $classes = [];
        $classNames = ['Nursery', 'LKG', 'UKG', '1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th', '11th Science', '11th Commerce', '11th Arts', '12th Science', '12th Commerce', '12th Arts'];
        // Let's create 25 classes by adding some more streams
        $extraClasses = ['11th Vocational', '12th Vocational', 'O Level', 'A Level', 'Pre-K', 'Playgroup'];
        $classNames = array_merge($classNames, $extraClasses);
        
        $sections = [];
        foreach ($classNames as $index => $cName) {
            $group = $groups[$index % count($groups)];
            $c = SchoolClass::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'class_group_id' => $group->id,
                'name' => $cName,
            ]);
            $classes[] = $c;

            // Sections A, B
            $sections[] = Section::create(['school_class_id' => $c->id, 'name' => 'A']);
            $sections[] = Section::create(['school_class_id' => $c->id, 'name' => 'B']);
        }

        // 5. Subject Types (2)
        $typeTheory = SubjectType::create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'Theory']);
        $typePractical = SubjectType::create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'Practical']);

        // 6. Subjects (Minimum 2 per class)
        $subjectNames = ['Mathematics', 'English', 'Science', 'History', 'Geography', 'Physics', 'Chemistry', 'Biology'];
        $subjects = [];
        foreach ($classes as $c) {
            $sub1 = Subject::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $c->id,
                'subject_type_id' => $typeTheory->id,
                'name' => 'Theory of ' . $c->name,
                'code' => 'TH-' . strtoupper(Str::slug($c->name))
            ]);
            $sub2 = Subject::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $c->id,
                'subject_type_id' => $typePractical->id,
                'name' => 'Practical of ' . $c->name,
                'code' => 'PR-' . strtoupper(Str::slug($c->name))
            ]);
            $subjects[] = $sub1;
            $subjects[] = $sub2;
        }

        // 7. Admins (10) and Teachers (50)
        // First ensure roles exist
        $adminRole = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Admin']);
        $teacherRole = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Teacher']);

        $admins = [];
        $randStr = strtolower(Str::random(4));
        for ($i = 1; $i <= 10; $i++) {
            $admins[] = SchoolUser::create([
                'school_id' => $school->id,
                'name' => 'Admin User ' . $i,
                'email' => "admin{$i}_{$randStr}@artemyrademo.edu",
                'phone' => '999' . rand(1000000, 9999999),
                'gender' => 'Male',
                'joining_date' => Carbon::now()->subMonths(10),
                'salary' => 50000,
                'status' => 'Active',
                'role' => 'Admin',
                'username' => "admin{$i}_{$randStr}",
                'password' => Hash::make('password')
            ]);
        }

        $teachers = [];
        for ($i = 1; $i <= 50; $i++) {
            $teachers[] = Staff::create([
                'school_id' => $school->id,
                'staff_role_id' => $teacherRole->id,
                'name' => 'Teacher User ' . $i,
                'email' => "teacher{$i}_{$randStr}@artemyrademo.edu",
                'phone' => '888' . rand(1000000, 9999999),
                'gender' => ($i % 2 == 0) ? 'Female' : 'Male',
                'joining_date' => Carbon::now()->subMonths(5),
                'salary' => 30000,
                'status' => 'Active',
                'username' => "teacher{$i}_{$randStr}",
                'password' => Hash::make('password')
            ]);
        }

        // 8. Students (100)
        $students = [];
        for ($i = 1; $i <= 100; $i++) {
            $c = $classes[array_rand($classes)];
            $cSections = Section::where('school_class_id', $c->id)->get();
            $s = $cSections->random();
            
            $students[] = Student::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $c->id,
                'section_id' => $s->id,
                'admission_no' => 'ADM-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . strtoupper(Str::random(2)),
                'first_name' => 'Student',
                'last_name' => "Demo$i",
                'gender' => ($i % 2 == 0) ? 'Female' : 'Male',
                'date_of_birth' => Carbon::now()->subYears(rand(5, 18)),
                'status' => 'Active'
            ]);
        }

        // 9. Exams (10)
        $examGroup = ExamGroup::create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'Main Exams']);
        for ($i = 1; $i <= 10; $i++) {
            $c = $classes[array_rand($classes)];
            Exam::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'school_class_id' => $c->id,
                'exam_group_id' => $examGroup->id,
                'title' => "Term Exam $i",
                'exam_center' => 'Main Campus',
                'start_date' => Carbon::now()->addDays($i * 10),
                'end_date' => Carbon::now()->addDays($i * 10 + 5)
            ]);
        }

        // 10. Fee Types (2 per class)
        $feePeriod = FeePeriod::firstOrCreate([
            'school_id' => $school->id, 
            'academic_session_id' => $session->id, 
            'name' => 'Monthly',
            'duration_months' => 1
        ]);
        foreach ($classes as $c) {
            $ft1 = FeeType::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'fee_period_id' => $feePeriod->id,
                'name' => 'Tuition Fee - ' . $c->name,
                'amount' => rand(1000, 5000),
                'invoice_on_admission' => false
            ]);
            $ft1->classes()->attach($c->id);

            $ft2 = FeeType::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'fee_period_id' => $feePeriod->id,
                'name' => 'Lab Fee - ' . $c->name,
                'amount' => rand(500, 1500),
                'invoice_on_admission' => false
            ]);
            $ft2->classes()->attach($c->id);
        }

        // 11. Transport Details
        $vehicles = [];
        for ($i = 1; $i <= 5; $i++) {
            $vehicles[] = Vehicle::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'vehicle_number' => 'AB' . rand(10, 99) . 'CD' . rand(1000, 9999),
                'vehicle_model' => 'Model ' . $i,
                'driver_name' => 'Driver ' . $i,
                'license_number' => 'LIC' . rand(100000, 999999),
                'driver_phone' => '777' . rand(1000000, 9999999)
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            $route = TransportRoute::create([
                'school_id' => $school->id,
                'academic_session_id' => $session->id,
                'route_name' => 'Route ' . $i,
                'route_fare' => rand(500, 2000),
                'period_id' => $feePeriod->id
            ]);
            $route->vehicles()->attach($vehicles[$i - 1]->id);
        }

        // 12. Additional Requested Entities
        // Medium
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'English', 'is_active' => true]);

        // Class Teachers (Assigning teachers to some classes)
        foreach (array_slice($classes, 0, 5) as $idx => $cls) {
            $sec = Section::where('school_class_id', $cls->id)->first();
            if ($sec && isset($teachers[$idx])) {
                ClassTeacher::create([
                    'school_id' => $school->id,
                    'session_id' => $session->id,
                    'class_id' => $cls->id,
                    'staff_id' => $teachers[$idx]->id
                ]);
            }
        }

        // Student Leaves
        for ($i = 0; $i < 5; $i++) {
            if (isset($students[$i])) {
                StudentLeave::create([
                    'school_id' => $school->id,
                    'session_id' => $session->id,
                    'student_id' => $students[$i]->id,
                    'class_id' => $classes[0]->id,
                    'section_id' => $sections[0]->id,
                    'leave_type' => 'Sick',
                    'start_date' => Carbon::now()->addDays($i),
                    'end_date' => Carbon::now()->addDays($i + 1),
                    'reason' => 'Fever',
                    'status' => 'Approved'
                ]);
            }
        }

        // Staff Leaves
        for ($i = 0; $i < 5; $i++) {
            if (isset($teachers[$i])) {
                StaffLeave::create([
                    'school_id' => $school->id,
                    'applicant_id' => $teachers[$i]->id,
                    'applicant_type' => 'staff',
                    'from_date' => Carbon::now()->addDays($i),
                    'to_date' => Carbon::now()->addDays($i + 1),
                    'leave_type' => 'Casual',
                    'reason' => 'Personal',
                    'status' => 'Approved'
                ]);
            }
        }

        // Study Material
        for ($i = 0; $i < 5; $i++) {
            if (isset($classes[0])) {
                StudyMaterial::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $session->id,
                    'book_name' => 'Demo Book ' . $i,
                    'title' => 'Demo Material ' . $i,
                    'description' => 'Description ' . $i,
                    'school_class_id' => $classes[0]->id,
                    'file_path' => 'demo/path.pdf',
                    'type' => 'pdf'
                ]);
            }
        }

        // Fee Invoices
        for ($i = 0; $i < 5; $i++) {
            if (isset($students[$i])) {
                FeeInvoice::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $session->id,
                    'student_id' => $students[$i]->id,
                    'invoice_title' => 'FEE-' . rand(1000, 9999),
                    'total_amount' => 5000,
                    'amount_paid' => 5000,
                    'due_amount' => 0,
                    'status' => 'paid',
                    'issued_at' => Carbon::now(),
                    'due_at' => Carbon::now()->addDays(30)
                ]);
            }
        }

        // Transport Invoices
        $route = TransportRoute::where('school_id', $school->id)->first();
        for ($i = 0; $i < 5; $i++) {
            if (isset($students[$i]) && $route) {
                TransportInvoice::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $session->id,
                    'student_id' => $students[$i]->id,
                    'transport_route_id' => $route->id,
                    'invoice_title' => 'TRN-' . rand(1000, 9999),
                    'issued_at' => Carbon::now(),
                    'total_amount' => 1000,
                    'amount_paid' => 0,
                    'due_amount' => 1000,
                    'status' => 'unpaid'
                ]);
            }
        }

        $this->command->info('Demo Data for Artemyra Academy generated successfully!');
    }
}
