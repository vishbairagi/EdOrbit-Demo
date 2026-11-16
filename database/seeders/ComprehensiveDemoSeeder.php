<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\{
    School, AcademicSession, ClassGroup, SchoolClass, Section,
    StaffRole, Staff, SchoolUser, SubjectType, Subject, Student,
    ExamGroup, Exam, ExamPaper, FeePeriod, FeeType, Vehicle,
    TransportRoute, Medium, ClassTeacher, StudentLeave, StudyMaterial,
    StaffLeave, FeeInvoice, FeePayment, TransportInvoice, TransportPayment,
    LibraryBook, LibraryBookIssue, NoticeBoard, SystemSetting, SchoolSetting,
    User, PaymentMethod
};

class ComprehensiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 0. Safely disable foreign key checks ──
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // Clean existing data for a fresh, perfect demo
        $tables = [
            'library_book_issues', 'library_books', 'transport_payments', 'transport_invoices',
            'student_transport_route', 'transport_route_vehicle', 'transport_routes', 'vehicles',
            'fee_payments', 'fee_type_section', 'fee_type_school_class', 'fee_invoices', 'fee_types', 'fee_periods',
            'notice_boards', 'study_materials', 'student_leaves', 'staff_leaves', 'class_teachers',
            'exam_results', 'exam_result_items', 'exam_papers', 'exams', 'exam_groups',
            'student_attendances', 'staff_attendances', 'staff_salary_records',
            'student_subjects', 'students', 'subjects', 'subject_types',
            'sections', 'school_classes', 'class_groups', 'mediums',
            'school_users', 'staff', 'role_permissions', 'staff_roles',
            'school_settings', 'academic_sessions', 'schools'
        ];

        foreach ($tables as $t) {
            try {
                DB::table($t)->truncate();
            } catch (\Throwable $e) {
                try {
                    DB::table($t)->delete();
                } catch (\Throwable $e2) {}
            }
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // ── 1. Master System Settings ──
        SystemSetting::setSetting('currency_symbol', '₹');
        SystemSetting::setSetting('currency_code', 'INR');
        SystemSetting::setSetting('date_format', 'd-m-Y');
        SystemSetting::setSetting('theme_color_primary', '#4F46E5');
        SystemSetting::setSetting('theme_color_secondary', '#1E1B4B');
        SystemSetting::setSetting('show_library_menu', '1');
        SystemSetting::setSetting('show_examination_menu', '1');
        SystemSetting::setSetting('show_transport_menu', '1');
        SystemSetting::setSetting('show_accounting_menu', '1');
        SystemSetting::setSetting('enable_bank_transfer', '1');
        SystemSetting::setSetting('enable_upi_transfer', '1');
        SystemSetting::setSetting('enable_cash_payment', '1');
        SystemSetting::setSetting('enable_check_payment', '1');

        // Ensure default test user exists if needed
        User::firstOrCreate(
            ['email' => 'admin@edorbit.com'],
            ['name' => 'Super Administrator', 'password' => Hash::make('password123')]
        );

        // ── 2. Define 6 Premier Realistic Schools ──
        $schoolsData = [
            [
                'name' => 'Delhi Public Global School, New Delhi',
                'code' => 'DPGS',
                'email' => 'admin@delhipublic.edu',
                'phone' => '+91 11 2803 4500',
                'address' => 'Sector 14, Dwarka, New Delhi - 110078',
                'description' => 'Premier CBSE institution dedicated to academic excellence, sports, and holistic character development.',
                'registration_number' => 'CBSE/AFF/2730381',
                'principal_name' => 'Dr. Rajeshwar Sharma',
                'principal_username' => 'dps_admin',
                'principal_email' => 'admin@delhipublic.edu',
                'principal_phone' => '9811023450',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'bus_prefix' => 'DL-01-EA-',
                'routes' => [
                    ['name' => 'Rohini - Pitampura Express', 'fare' => 2200],
                    ['name' => 'Dwarka Sector 10 - Janakpuri Line', 'fare' => 1800],
                    ['name' => 'Connaught Place - South Extension Corridor', 'fare' => 2500],
                    ['name' => 'Vasant Kunj - Saket Fast Track', 'fare' => 2100],
                ],
            ],
            [
                'name' => "St. Xavier's International Academy, Mumbai",
                'code' => 'SXIA',
                'email' => 'admin@stxaviers.edu',
                'phone' => '+91 22 2262 0661',
                'address' => '5 Mahapalika Marg, Dhobi Talao, Fort, Mumbai - 400001',
                'description' => 'A heritage ICSE institution offering world-class academics, arts, values, and leadership training.',
                'registration_number' => 'ICSE/MH/04289',
                'principal_name' => "Fr. Thomas D'Souza",
                'principal_username' => 'xavier_admin',
                'principal_email' => 'admin@stxaviers.edu',
                'principal_phone' => '9820034567',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'bus_prefix' => 'MH-01-CB-',
                'routes' => [
                    ['name' => 'Marine Drive - Churchgate Express', 'fare' => 2400],
                    ['name' => 'Bandra West - Khar Danda Route', 'fare' => 2600],
                    ['name' => 'Andheri East - Powai Link', 'fare' => 2100],
                    ['name' => 'Juhu Tara - Santacruz West Line', 'fare' => 2300],
                ],
            ],
            [
                'name' => 'The Heritage Valley School, Bengaluru',
                'code' => 'THVS',
                'email' => 'admin@heritagevalley.edu',
                'phone' => '+91 80 4125 7800',
                'address' => 'ITPL Main Road, Whitefield, Bengaluru - 560066',
                'description' => 'Innovating future education with state-of-the-art STEM laboratories, global curriculum, and sustainable campus.',
                'registration_number' => 'KA/CBSE/18920',
                'principal_name' => 'Dr. K. S. Venkatesh Murthy',
                'principal_username' => 'heritage_admin',
                'principal_email' => 'admin@heritagevalley.edu',
                'principal_phone' => '9845012345',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'bus_prefix' => 'KA-04-AB-',
                'routes' => [
                    ['name' => 'Whitefield - ITPL Tech Corridor', 'fare' => 2100],
                    ['name' => 'Indiranagar - Old Airport Road Line', 'fare' => 2500],
                    ['name' => 'Koramangala - HSR Sector 2 Route', 'fare' => 2400],
                    ['name' => 'Electronic City Expressway Shuttle', 'fare' => 2800],
                ],
            ],
            [
                'name' => 'Oakridge International School, Hyderabad',
                'code' => 'OIS',
                'email' => 'admin@oakridge.edu',
                'phone' => '+91 40 4567 8900',
                'address' => 'Financial District, Nanakramguda, Gachibowli, Hyderabad - 500032',
                'description' => 'A world-class Cambridge and CBSE day-boarding school empowering young minds towards global leadership.',
                'registration_number' => 'TS/CAM/55210',
                'principal_name' => 'Dr. S. K. Naidu',
                'principal_username' => 'oakridge_admin',
                'principal_email' => 'admin@oakridge.edu',
                'principal_phone' => '9849098765',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'bus_prefix' => 'TS-09-UB-',
                'routes' => [
                    ['name' => 'Banjara Hills - Jubilee Hills Route', 'fare' => 2600],
                    ['name' => 'Gachibowli - HITEC City Express', 'fare' => 1900],
                    ['name' => 'Kondapur - Madhapur Link', 'fare' => 2000],
                    ['name' => 'Manikonda - Puppalguda Shuttle', 'fare' => 1800],
                ],
            ],
            [
                'name' => 'Silver Bells High School, Ahmedabad',
                'code' => 'SBHS',
                'email' => 'admin@silverbells.edu',
                'phone' => '+91 79 2685 4100',
                'address' => 'SG Highway, Near Thaltej Cross Roads, Ahmedabad - 380054',
                'description' => 'Fostering excellence in science, commerce, entrepreneurial mindset, and vibrant student community.',
                'registration_number' => 'GJ/CBSE/78219',
                'principal_name' => 'Bhavin S. Shah',
                'principal_username' => 'silverbells_admin',
                'principal_email' => 'admin@silverbells.edu',
                'principal_phone' => '9825087654',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'bus_prefix' => 'GJ-01-AX-',
                'routes' => [
                    ['name' => 'SG Highway - Thaltej Corridor', 'fare' => 1700],
                    ['name' => 'Satellite - Vastrapur Lake Route', 'fare' => 1900],
                    ['name' => 'Bopal - South Bopal Shuttle', 'fare' => 1600],
                    ['name' => 'Navrangpura - CG Road Express', 'fare' => 2000],
                ],
            ],
            [
                'name' => 'Greenwood Convent Senior Secondary School, Jaipur',
                'code' => 'GCSS',
                'email' => 'admin@greenwood.edu',
                'phone' => '+91 141 278 1200',
                'address' => 'Madhyam Marg, Mansarovar, Jaipur - 302020',
                'description' => 'Inspiring excellence, discipline, Indian cultural heritage, and modern digital education.',
                'registration_number' => 'RJ/CBSE/34901',
                'principal_name' => 'Col. (Retd.) Mahendra Singh Shekhawat',
                'principal_username' => 'greenwood_admin',
                'principal_email' => 'admin@greenwood.edu',
                'principal_phone' => '9829011223',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'bus_prefix' => 'RJ-14-PC-',
                'routes' => [
                    ['name' => 'Mansarovar - Gopalpura Bypass Line', 'fare' => 1600],
                    ['name' => 'Vaishali Nagar - Queens Road Route', 'fare' => 1900],
                    ['name' => 'Malviya Nagar - Jawahar Circle Express', 'fare' => 2100],
                    ['name' => 'C-Scheme - Tonk Road Shuttle', 'fare' => 1800],
                ],
            ],
        ];

        // ── 3. Realistic Names Pool ──
        $indianBoys = [
            'Aarav', 'Rohan', 'Vihaan', 'Arjun', 'Ishaan', 'Aditya', 'Kabir', 'Aryan',
            'Devansh', 'Siddharth', 'Yash', 'Pranav', 'Varun', 'Tejas', 'Akhil', 'Sai',
            'Abhinav', 'Nithin', 'Harshil', 'Aarsh', 'Shlok', 'Jainam', 'Digvijay',
            'Harshvardhan', 'Devraj', 'Manvendra', 'Ethan', 'Neil', 'David', 'Michael',
            'Daniel', 'Ronald', 'Reyansh', 'Dhruv', 'Ayush', 'Rudra', 'Shaurya', 'Aayush'
        ];

        $indianGirls = [
            'Diya', 'Ananya', 'Kavya', 'Meera', 'Riya', 'Tanvi', 'Saanvi', 'Kiara',
            'Avani', 'Alisha', 'Chloe', 'Samhita', 'Anika', 'Niharika', 'Shreya', 'Harika',
            'Siri', 'Manasvi', 'Tanya', 'Dhwani', 'Priyanshi', 'Krisha', 'Prisha', 'Pallavi',
            'Shivani', 'Tamanna', 'Ritu', 'Sarah', 'Jennifer', 'Ashley', 'Pooja', 'Sneha',
            'Isha', 'Aadhya', 'Anushka', 'Tara', 'Navya', 'Myra', 'Diya', 'Trisha'
        ];

        $lastNames = [
            'Sharma', 'Patel', 'Verma', 'Iyer', 'Gupta', 'Mehta', 'Joshi', 'Reddy',
            'Nair', 'Choudhary', 'Sen', 'Malhotra', 'Deshmukh', 'Kapoor', 'Kulkarni',
            'Singhania', "D'Souza", 'Fernandez', 'Kadam', 'Shenoy', 'Tendulkar', 'Sawant',
            'Venkatesh', 'Murthy', 'Sundaram', 'Krishnan', 'Hegde', 'Raghunathan', 'Bhat',
            'Naidu', 'Ramana', 'Chigurupati', 'Rao', 'Konda', 'Shah', 'Trivedi', 'Modi',
            'Bhatt', 'Solanki', 'Dave', 'Parmar', 'Rathore', 'Shekhawat', 'Kanwar',
            'Bairwa', 'Meena', 'Soni', 'Miller', 'Walker', 'Smith', 'Pinto', 'Lobo'
        ];

        $teacherProfiles = [
            ['name' => 'Rajesh Sharma', 'gender' => 'Male', 'desig' => 'Senior PGT Physics', 'subject' => 'Physics'],
            ['name' => 'Priya Nair', 'gender' => 'Female', 'desig' => 'PGT Mathematics', 'subject' => 'Mathematics'],
            ['name' => 'David Miller', 'gender' => 'Male', 'desig' => 'TGT English Literature', 'subject' => 'English'],
            ['name' => 'Ananya Verma', 'gender' => 'Female', 'desig' => 'PGT Chemistry', 'subject' => 'Chemistry'],
            ['name' => 'Vikram Malhotra', 'gender' => 'Male', 'desig' => 'TGT Social Sciences', 'subject' => 'History'],
            ['name' => 'Sneha Patel', 'gender' => 'Female', 'desig' => 'PGT Biology', 'subject' => 'Biology'],
            ['name' => 'Amit Sengupta', 'gender' => 'Male', 'desig' => 'PGT Computer Science', 'subject' => 'Computer Science'],
            ['name' => 'Sunita Rao', 'gender' => 'Female', 'desig' => 'TGT Hindi Vyakaran', 'subject' => 'Hindi'],
            ['name' => 'Sarah Jenkins', 'gender' => 'Female', 'desig' => 'PRT Primary Coordinator', 'subject' => 'Science'],
            ['name' => 'Arvind Swaminathan', 'gender' => 'Male', 'desig' => 'PGT Accountancy & Commerce', 'subject' => 'Accountancy'],
            ['name' => 'Jennifer Fernandez', 'gender' => 'Female', 'desig' => 'TGT Economics', 'subject' => 'Economics'],
            ['name' => 'Rohan Kulkarni', 'gender' => 'Male', 'desig' => 'Sports & Physical Education Director', 'subject' => 'Physical Education'],
            ['name' => 'Pooja Agarwal', 'gender' => 'Female', 'desig' => 'Senior Librarian', 'subject' => 'Library'],
            ['name' => 'Michael DSouza', 'gender' => 'Male', 'desig' => 'Chief Accountant & Finance', 'subject' => 'Accounts'],
        ];

        $bookCatalog = [
            ['title' => 'Concepts of Physics (Vol 1 & 2)', 'author' => 'Prof. H.C. Verma', 'subject' => 'Physics', 'price' => 750, 'rack' => 'PH-01'],
            ['title' => 'Mathematics for Class 10 & 12', 'author' => 'Dr. R.D. Sharma', 'subject' => 'Mathematics', 'price' => 890, 'rack' => 'MT-02'],
            ['title' => 'Wings of Fire - An Autobiography', 'author' => 'Dr. A.P.J. Abdul Kalam', 'subject' => 'Literature', 'price' => 350, 'rack' => 'LT-01'],
            ['title' => 'Malgudi Days & The Guide', 'author' => 'R.K. Narayan', 'subject' => 'English Literature', 'price' => 299, 'rack' => 'LT-03'],
            ['title' => 'The Discovery of India', 'author' => 'Jawaharlal Nehru', 'subject' => 'History', 'price' => 595, 'rack' => 'HS-01'],
            ['title' => 'Fundamentals of Physics', 'author' => 'Halliday, Resnick & Walker', 'subject' => 'Physics', 'price' => 1250, 'rack' => 'PH-04'],
            ['title' => 'Organic Chemistry Principles', 'author' => 'Morrison and Boyd', 'subject' => 'Chemistry', 'price' => 980, 'rack' => 'CH-01'],
            ['title' => 'NCERT Exemplar Problems - Biology', 'author' => 'NCERT Editorial Board', 'subject' => 'Biology', 'price' => 240, 'rack' => 'BIO-01'],
            ['title' => 'Python Programming for School AI', 'author' => 'Sumita Arora', 'subject' => 'Computer Science', 'price' => 620, 'rack' => 'CS-02'],
            ['title' => 'Double Entry Book Keeping & Financial Accounting', 'author' => 'T.S. Grewal', 'subject' => 'Accountancy', 'price' => 780, 'rack' => 'AC-01'],
            ['title' => 'Introductory Micro & Macro Economics', 'author' => 'Sandeep Garg', 'subject' => 'Economics', 'price' => 540, 'rack' => 'EC-01'],
            ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'subject' => 'Classic English', 'price' => 399, 'rack' => 'LT-05'],
            ['title' => 'Harry Potter and the Sorcerer’s Stone', 'author' => 'J.K. Rowling', 'subject' => 'Fiction', 'price' => 499, 'rack' => 'FK-01'],
            ['title' => 'Oxford Advanced Learner’s Dictionary', 'author' => 'Oxford University Press', 'subject' => 'Reference', 'price' => 850, 'rack' => 'RF-01'],
            ['title' => 'Gitanjali - Song Offerings', 'author' => 'Rabindranath Tagore', 'subject' => 'Poetry', 'price' => 220, 'rack' => 'LT-02'],
        ];

        $globalActiveSession = null;

        // ── 4. Iterate over each school and seed full, deep data ──
        foreach ($schoolsData as $sIdx => $sd) {
            // Create School Record
            $school = School::create([
                'name' => $sd['name'],
                'email' => $sd['email'],
                'phone' => $sd['phone'],
                'address' => $sd['address'],
                'description' => $sd['description'],
                'registration_number' => $sd['registration_number'],
                'status' => 1,
                'enrollment_prefix' => $sd['code'] . '-ENR-',
                'enrollment_base_number' => 1000,
                'enrollment_base_padding' => 4,
                'admission_prefix' => $sd['code'] . '-ADM-',
                'admission_base_number' => 100,
                'admission_base_padding' => 4,
            ]);

            // Academic Sessions (2025-2026 and 2026-2027)
            $pastSession = AcademicSession::create([
                'name' => '2025-2026',
                'start_date' => Carbon::parse('2025-04-01'),
                'end_date' => Carbon::parse('2026-03-31'),
                'status' => 'inactive',
            ]);

            $activeSession = AcademicSession::create([
                'name' => '2026-2027',
                'start_date' => Carbon::parse('2026-04-01'),
                'end_date' => Carbon::parse('2027-03-31'),
                'status' => 'active',
            ]);

            if (!$globalActiveSession) {
                $globalActiveSession = $activeSession;
                SystemSetting::setSetting('current_session_id', $activeSession->id);
            }

            // Set School Currency & Session Settings
            SchoolSetting::setSetting($school->id, $activeSession->id, 'currency', 'INR');
            SchoolSetting::setSetting($school->id, $activeSession->id, 'currency_symbol', '₹');
            SchoolSetting::setSetting($school->id, $activeSession->id, 'date_format', 'd-m-Y');
            SchoolSetting::setSetting($school->id, $activeSession->id, 'school_name', $school->name);
            SchoolSetting::setSetting($school->id, $activeSession->id, 'school_email', $school->email);
            SchoolSetting::setSetting($school->id, $activeSession->id, 'school_phone', $school->phone);
            SchoolSetting::setSetting($school->id, $activeSession->id, 'school_address', $school->address);

            // Mediums
            $medEnglish = Medium::create(['school_id' => $school->id, 'name' => 'English', 'is_active' => true]);
            $medHindi = Medium::create(['school_id' => $school->id, 'name' => 'Hindi', 'is_active' => true]);

            // Roles
            $roleAdmin = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Admin']);
            $roleTeacher = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Teacher']);
            $roleAccountant = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Accountant']);
            $roleLibrarian = StaffRole::firstOrCreate(['school_id' => $school->id, 'name' => 'Librarian']);

            // School Principal / Administrator (SchoolUser)
            SchoolUser::create([
                'school_id' => $school->id,
                'name' => $sd['principal_name'],
                'email' => $sd['principal_email'],
                'login_email' => $sd['principal_email'],
                'phone' => $sd['principal_phone'],
                'login_phone' => $sd['principal_phone'],
                'gender' => 'Male',
                'joining_date' => Carbon::now()->subYears(3),
                'salary' => 95000,
                'status' => 'Active',
                'role' => 'Admin',
                'username' => $sd['principal_username'],
                'password' => Hash::make('password123'),
            ]);

            // Staff & Teachers
            $createdStaff = [];
            $schoolDomain = explode('@', $sd['email'])[1];
            foreach ($teacherProfiles as $tIdx => $tp) {
                $staffSlug = strtolower(Str::slug($tp['name']));
                $staffUsername = strtolower($sd['code']) . '.' . $staffSlug;
                $staffEmail = $staffSlug . '.' . strtolower($sd['code']) . '@' . $schoolDomain;

                $createdStaff[] = Staff::create([
                    'school_id' => $school->id,
                    'staff_role_id' => $roleTeacher->id,
                    'employee_id' => $sd['code'] . '-EMP-' . str_pad($tIdx + 1, 3, '0', STR_PAD_LEFT),
                    'name' => $tp['name'],
                    'email' => $staffEmail,
                    'login_email' => $staffEmail,
                    'phone' => '98' . rand(10000000, 99999999),
                    'login_phone' => '98' . rand(10000000, 99999999),
                    'gender' => $tp['gender'],
                    'designation' => $tp['desig'],
                    'joining_date' => Carbon::now()->subMonths(rand(6, 36)),
                    'salary' => rand(38, 75) * 1000,
                    'status' => 'Active',
                    'username' => $staffUsername,
                    'password' => Hash::make('password123'),
                ]);
            }

            // Class Groups
            $cgPrePrimary = ClassGroup::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Foundational & Pre-Primary', 'status' => 1]);
            $cgPrimary = ClassGroup::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Primary Wing (Grades 1-5)', 'status' => 1]);
            $cgMiddle = ClassGroup::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Middle Wing (Grades 6-8)', 'status' => 1]);
            $cgSenior = ClassGroup::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Senior Secondary (Grades 9-12)', 'status' => 1]);

            // Classes definition
            $classSpecs = [
                ['name' => 'Nursery', 'group' => $cgPrePrimary],
                ['name' => 'Kindergarten (KG)', 'group' => $cgPrePrimary],
                ['name' => 'Grade 1', 'group' => $cgPrimary],
                ['name' => 'Grade 2', 'group' => $cgPrimary],
                ['name' => 'Grade 3', 'group' => $cgPrimary],
                ['name' => 'Grade 4', 'group' => $cgPrimary],
                ['name' => 'Grade 5', 'group' => $cgPrimary],
                ['name' => 'Grade 6', 'group' => $cgMiddle],
                ['name' => 'Grade 7', 'group' => $cgMiddle],
                ['name' => 'Grade 8', 'group' => $cgMiddle],
                ['name' => 'Grade 9', 'group' => $cgSenior],
                ['name' => 'Grade 10', 'group' => $cgSenior],
                ['name' => 'Grade 11 (Science)', 'group' => $cgSenior],
                ['name' => 'Grade 11 (Commerce)', 'group' => $cgSenior],
                ['name' => 'Grade 12 (Science)', 'group' => $cgSenior],
                ['name' => 'Grade 12 (Commerce)', 'group' => $cgSenior],
            ];

            $createdClasses = [];
            $allSections = [];

            foreach ($classSpecs as $csIdx => $cs) {
                $c = SchoolClass::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'class_group_id' => $cs['group']->id,
                    'name' => $cs['name'],
                ]);
                $createdClasses[] = $c;

                // Sections A and B
                $secA = Section::create(['school_class_id' => $c->id, 'name' => 'Section A']);
                $secB = Section::create(['school_class_id' => $c->id, 'name' => 'Section B']);
                $allSections[$c->id] = [$secA, $secB];

                // Assign Class Teacher to Section A
                if (isset($createdStaff[$csIdx % count($createdStaff)])) {
                    ClassTeacher::create([
                        'school_id' => $school->id,
                        'session_id' => $activeSession->id,
                        'class_id' => $c->id,
                        'section_id' => $secA->id,
                        'staff_id' => $createdStaff[$csIdx % count($createdStaff)]->id,
                    ]);
                }
            }

            // Subject Types
            $stTheory = SubjectType::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Theory']);
            $stPractical = SubjectType::create(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Practical / Lab']);

            // Subjects for each class
            $standardSubjects = [
                ['name' => 'Mathematics', 'type' => $stTheory, 'code' => 'MATH'],
                ['name' => 'English Core', 'type' => $stTheory, 'code' => 'ENG'],
                ['name' => 'General Science', 'type' => $stTheory, 'code' => 'SCI'],
                ['name' => 'Social Science', 'type' => $stTheory, 'code' => 'SST'],
                ['name' => 'Computer Applications & AI', 'type' => $stPractical, 'code' => 'CAI'],
                ['name' => 'Physics Laboratory', 'type' => $stPractical, 'code' => 'PHY-LAB'],
            ];

            foreach ($createdClasses as $c) {
                foreach ($standardSubjects as $subj) {
                    Subject::create([
                        'school_id' => $school->id,
                        'academic_session_id' => $activeSession->id,
                        'school_class_id' => $c->id,
                        'subject_type_id' => $subj['type']->id,
                        'name' => $subj['name'],
                        'code' => $subj['code'] . '-' . Str::slug($c->name),
                    ]);
                }
            }

            // Payment Methods
            $pmUPI = PaymentMethod::firstOrCreate(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'UPI / QR Code'], ['is_active' => true]);
            $pmCard = PaymentMethod::firstOrCreate(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Credit / Debit Card'], ['is_active' => true]);
            $pmBank = PaymentMethod::firstOrCreate(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Net Banking / NEFT'], ['is_active' => true]);
            $pmCash = PaymentMethod::firstOrCreate(['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Cash Counter'], ['is_active' => true]);
            $paymentMethods = [$pmUPI, $pmCard, $pmBank, $pmCash];

            // Fee Period
            $feeQuarterly = FeePeriod::firstOrCreate(
                ['school_id' => $school->id, 'academic_session_id' => $activeSession->id, 'name' => 'Quarterly'],
                ['duration_months' => 3]
            );

            // Fee Types
            $feeTuition = FeeType::create([
                'school_id' => $school->id,
                'academic_session_id' => $activeSession->id,
                'fee_period_id' => $feeQuarterly->id,
                'name' => 'Quarterly Tuition & Academic Fee',
                'amount' => 12500,
                'invoice_on_admission' => false,
            ]);
            $feeTuition->classes()->attach(collect($createdClasses)->pluck('id'));

            $feeLab = FeeType::create([
                'school_id' => $school->id,
                'academic_session_id' => $activeSession->id,
                'fee_period_id' => $feeQuarterly->id,
                'name' => 'STEM, Robotics & Computer Lab Fee',
                'amount' => 3500,
                'invoice_on_admission' => false,
            ]);
            $feeLab->classes()->attach(collect($createdClasses)->pluck('id'));

            $feeSports = FeeType::create([
                'school_id' => $school->id,
                'academic_session_id' => $activeSession->id,
                'fee_period_id' => $feeQuarterly->id,
                'name' => 'Sports & Annual Activity Fee',
                'amount' => 2000,
                'invoice_on_admission' => false,
            ]);
            $feeSports->classes()->attach(collect($createdClasses)->pluck('id'));

            // Transport: Vehicles and Routes
            $vehicles = [];
            for ($v = 1; $v <= 4; $v++) {
                $vehicles[] = Vehicle::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'vehicle_number' => $sd['bus_prefix'] . rand(1000, 9999),
                    'vehicle_model' => 'Tata Starbus Ultra AC 40-Seater',
                    'driver_name' => $lastNames[($sIdx * 4 + $v) % count($lastNames)] . ' Driver Ram',
                    'driver_phone' => '97' . rand(10000000, 99999999),
                    'license_number' => 'DL-' . $sd['code'] . '-' . rand(100000, 999999),
                ]);
            }

            $routes = [];
            foreach ($sd['routes'] as $rIdx => $rData) {
                $route = TransportRoute::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'route_name' => $rData['name'],
                    'route_fare' => $rData['fare'],
                    'period_id' => $feeQuarterly->id,
                ]);
                $route->vehicles()->attach($vehicles[$rIdx % count($vehicles)]->id);
                $routes[] = $route;
            }

            // Students (45 per school across classes)
            $createdStudents = [];
            $studentCount = 45;
            for ($st = 1; $st <= $studentCount; $st++) {
                $isBoy = ($st % 2 !== 0);
                $firstName = $isBoy ? $indianBoys[$st % count($indianBoys)] : $indianGirls[$st % count($indianGirls)];
                $lastName = $lastNames[($st + $sIdx * 7) % count($lastNames)];
                $fatherFirstName = $indianBoys[($st + 3) % count($indianBoys)];
                $motherFirstName = $indianGirls[($st + 4) % count($indianGirls)];

                $targetClass = $createdClasses[$st % count($createdClasses)];
                $targetSections = $allSections[$targetClass->id];
                $targetSection = $targetSections[$st % count($targetSections)];

                $student = Student::create([
                    'admission_no' => $sd['code'] . '-2026-' . str_pad($st, 4, '0', STR_PAD_LEFT),
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'school_class_id' => $targetClass->id,
                    'section_id' => $targetSection->id,
                    'medium_id' => $medEnglish->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $isBoy ? 'Male' : 'Female',
                    'date_of_birth' => Carbon::now()->subYears(rand(6, 17))->subDays(rand(10, 300)),
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+'][rand(0, 3)],
                    'address' => 'House #' . rand(12, 450) . ', ' . $sd['city'],
                    'phone' => '98' . rand(10000000, 99999999),
                    'email' => strtolower($firstName . '.' . $lastName . '.' . $sd['code'] . $st) . '@student.edu',
                    'city' => $sd['city'],
                    'state' => $sd['state'],
                    'country' => 'India',
                    'father_name' => $fatherFirstName . ' ' . $lastName,
                    'father_phone' => '98' . rand(10000000, 99999999),
                    'father_occupation' => ['Engineer', 'Doctor', 'Chartered Accountant', 'Business Owner', 'Professor', 'Civil Servant'][rand(0, 5)],
                    'mother_name' => $motherFirstName . ' ' . $lastName,
                    'mother_phone' => '98' . rand(10000000, 99999999),
                    'mother_occupation' => ['Architect', 'Software Consultant', 'Teacher', 'Bank Manager', 'Homemaker'][rand(0, 4)],
                    'roll_number' => (string) str_pad(($st % 30) + 1, 2, '0', STR_PAD_LEFT),
                    'status' => 'Active',
                ]);
                $createdStudents[] = $student;

                // Assign transport to ~40% of students
                if ($st % 3 === 0 && !empty($routes)) {
                    $selectedRoute = $routes[$st % count($routes)];
                    $student->transportRoutes()->attach($selectedRoute->id);

                    // Create Transport Invoice
                    $trnTotal = $selectedRoute->route_fare;
                    $trnPaid = ($st % 6 === 0) ? 0 : $trnTotal;
                    $trnStatus = ($trnPaid == $trnTotal) ? 'paid' : 'unpaid';

                    TransportInvoice::create([
                        'school_id' => $school->id,
                        'academic_session_id' => $activeSession->id,
                        'student_id' => $student->id,
                        'transport_route_id' => $selectedRoute->id,
                        'invoice_title' => 'TRN-' . $sd['code'] . '-' . date('Y') . '-' . str_pad($st, 3, '0', STR_PAD_LEFT),
                        'issued_at' => Carbon::now()->subDays(rand(5, 30)),
                        'start_date' => Carbon::now()->subDays(30),
                        'end_date' => Carbon::now()->addDays(60),
                        'total_amount' => $trnTotal,
                        'amount_paid' => $trnPaid,
                        'due_amount' => max(0, $trnTotal - $trnPaid),
                        'status' => $trnStatus,
                    ]);
                }

                // Create Fee Invoices for this student
                $totalFee = 18000; // Tuition + Lab + Sports
                $randPay = rand(1, 3);
                if ($randPay === 1) {
                    $paid = $totalFee;
                    $due = 0;
                    $invStatus = 'paid';
                } elseif ($randPay === 2) {
                    $paid = 10000;
                    $due = 8000;
                    $invStatus = 'partial';
                } else {
                    $paid = 0;
                    $due = $totalFee;
                    $invStatus = 'unpaid';
                }

                $inv = FeeInvoice::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'student_id' => $student->id,
                    'fee_type_id' => $feeTuition->id,
                    'invoice_title' => 'INV-' . $sd['code'] . '-Q1-' . str_pad($st, 4, '0', STR_PAD_LEFT),
                    'description' => 'Term 1 Comprehensive Academic & Development Fees',
                    'allow_partial_payment' => true,
                    'total_amount' => $totalFee,
                    'amount_paid' => $paid,
                    'due_amount' => $due,
                    'issued_at' => Carbon::now()->subDays(rand(10, 45)),
                    'due_at' => Carbon::now()->addDays(rand(5, 20)),
                    'status' => $invStatus,
                ]);

                if ($paid > 0) {
                    $selectedPm = $paymentMethods[rand(0, count($paymentMethods) - 1)];
                    FeePayment::create([
                        'school_id' => $school->id,
                        'fee_invoice_id' => $inv->id,
                        'payment_method_id' => $selectedPm->id,
                        'amount' => $paid,
                        'transaction_id' => 'TXN' . rand(10000000, 99999999),
                        'authorized_by' => 'Central Accounts',
                        'paid_on' => Carbon::now()->subDays(rand(1, 10)),
                        'note' => 'Quarter 1 Tuition & Lab Fees Received via ' . $selectedPm->name,
                    ]);
                }
            }

            // Examinations
            $examGroup = ExamGroup::create([
                'school_id' => $school->id,
                'academic_session_id' => $activeSession->id,
                'name' => 'Mid-Term & Periodic Assessment 2026',
            ]);

            for ($e = 1; $e <= 4; $e++) {
                $targetClass = $createdClasses[rand(4, count($createdClasses) - 1)];
                Exam::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'school_class_id' => $targetClass->id,
                    'exam_group_id' => $examGroup->id,
                    'title' => 'Term Assessment ' . $e . ' - ' . $targetClass->name,
                    'exam_center' => 'Main Academic Block, Hall ' . chr(64 + $e),
                    'start_date' => Carbon::now()->addDays($e * 7),
                    'end_date' => Carbon::now()->addDays($e * 7 + 4),
                ]);
            }

            // Library Books
            foreach ($bookCatalog as $bIdx => $bc) {
                LibraryBook::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'book_type' => 'Standard',
                    'title' => $bc['title'],
                    'author' => $bc['author'],
                    'subject' => $bc['subject'],
                    'rack_number' => $bc['rack'],
                    'book_number' => 'BK-' . $sd['code'] . '-' . str_pad($bIdx + 1, 4, '0', STR_PAD_LEFT),
                    'isbn_number' => '978-' . rand(10, 99) . '-' . rand(1000, 9999) . '-' . rand(10, 99) . '-' . rand(1, 9),
                    'price' => $bc['price'],
                    'quantity' => rand(5, 15),
                    'description' => 'Prescribed reference edition for library accession.',
                ]);
            }

            // Notice Board
            $notices = [
                [
                    'title' => 'Annual Inter-School Sports Meet 2026',
                    'desc' => 'Registration is now open for athletics, football, basketball, and chess tournaments. Students should submit entries to the physical education department by Friday.',
                ],
                [
                    'title' => 'Parent-Teacher Meeting (PTM) for Term 1',
                    'desc' => 'The Term 1 PTM is scheduled for upcoming Saturday from 9:00 AM to 1:00 PM. Parents are requested to adhere to their designated time slots.',
                ],
                [
                    'title' => 'CBSE & ICSE Board Examination Registration',
                    'desc' => 'Students of Grade 10 and Grade 12 must complete verification of their subject lists, registration photos, and DOB certificates by the end of this week.',
                ],
                [
                    'title' => 'STEM & Robotics Exhibition 2026',
                    'desc' => 'Selected student teams from Grade 6 to Grade 11 will demonstrate innovative AI, IoT, and solar energy prototypes in the central auditorium.',
                ],
            ];

            foreach ($notices as $nData) {
                NoticeBoard::create([
                    'school_id' => $school->id,
                    'academic_session_id' => $activeSession->id,
                    'title' => $nData['title'],
                    'description' => $nData['desc'],
                    'link_type' => 'none',
                    'is_active' => true,
                    'status' => 'Published',
                    'added_by' => 'Principal Office',
                ]);
            }
        }

        $this->command->info('Successfully seeded 6 premier schools with 100% realistic data, Indian & English names, staff, students, fees, buses, library, and exams!');
    }
}
