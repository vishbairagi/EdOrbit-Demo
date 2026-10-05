<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassTeacher;
use App\Models\FeeInvoice;
use App\Models\FeeType;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentType;
use App\Models\Subject;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    private function currentSchool(): ?School
    {
        $id = session('selected_school_id');

        return $id ? School::find($id) : null;
    }

    private function currentSession(): ?AcademicSession
    {
        $id = session('selected_session_id') ?: SystemSetting::getSetting('current_session_id');

        return $id ? AcademicSession::find($id) : null;
    }

    /** SM Student Dashboard */
    public function dashboard(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $query = $school
            ? Student::where('school_id', $school->id)
            : Student::query();

        if ($session) {
            $query->where('academic_session_id', $session->id);
        }

        $totalStudents = (clone $query)->count();
        $totalGirls = (clone $query)->where('gender', 'Female')->count();
        $totalBoys = (clone $query)->where('gender', 'Male')->count();
        $promoted = (clone $query)->where('status', 'Promoted')->count();
        $transferred = (clone $query)->where('status', 'Transferred')->count();

        return view('student.dashboard', compact(
            'school', 'session', 'totalStudents', 'totalGirls', 'totalBoys', 'promoted', 'transferred'
        ));
    }

    /** List all students */
    public function index(Request $request): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $query = Student::with(['schoolClass', 'section'])
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->when($session, fn ($q) => $q->where('academic_session_id', $session->id));

        $user = session('auth_guard') === 'staff' ? Staff::find(session('auth_user_id')) : null;
        $classIds = null;
        if ($user && ! $user->hasPermission('sm-student.students') && $session) {
            $classIds = ClassTeacher::where('staff_id', $user->id)
                ->where('session_id', $session->id)
                ->pluck('class_id')->toArray();
            $query->whereIn('school_class_id', $classIds);
        }

        // Search by keyword
        if ($request->filled('keyword') && $request->search_type === 'keyword') {
            $kw = $request->keyword;
            $field = $request->search_field ?? 'first_name';
            $query->where($field, 'like', "%{$kw}%");
        }

        // Search by class
        if ($request->filled('class_id') && $request->search_type === 'class') {
            $query->where('school_class_id', $request->class_id);
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }
        }

        $students = $query->orderBy('first_name')->paginate(25)->withQueryString();

        $classesQuery = SchoolClass::where('school_id', $school?->id)->where('academic_session_id', $session?->id)->orderBy('name');
        if (is_array($classIds)) {
            $classesQuery->whereIn('id', $classIds);
        }
        $classes = ($school && $session) ? $classesQuery->get() : collect();

        return view('student.students.index', compact('students', 'classes', 'school', 'session'));
    }

    /** Show create / admission form */
    public function create(): View
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        $classes = ($school && $session)
            ? SchoolClass::where('school_id', $school->id)->where('academic_session_id', $session->id)->orderBy('name')->get()
            : collect();

        $admissionNo = $school ? Student::generateAdmissionNo($school->id) : 'ADM00001';
        $categories = $school
            ? StudentCategory::where('school_id', $school->id)->orderBy('name')->pluck('name')
            : collect();
        $studentTypes = $school
            ? StudentType::where('school_id', $school->id)->orderBy('name')->get()
            : collect();
        $mediums = $school
            ? \App\Models\Medium::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('student.students.create', compact('school', 'session', 'classes', 'admissionNo', 'categories', 'studentTypes', 'mediums'));
    }

    /** Store new student */
    public function store(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $request->validate([
            'first_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')
                ->where('school_id', $school?->id)
                ->where('academic_session_id', $session?->id)],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where('school_class_id', $request->integer('school_class_id'))],
            'category' => ['nullable', 'string', Rule::exists('student_categories', 'name')->where('school_id', $school?->id)],
            'student_type_id' => ['nullable', 'integer', Rule::exists('student_types', 'id')->where('school_id', $school?->id)],
            'medium_id' => ['nullable', 'integer', Rule::exists('mediums', 'id')->where('school_id', $school?->id)],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'distinct', Rule::exists('subjects', 'id')->where('school_class_id', $request->integer('school_class_id'))->where('is_active', true)],
            'fee_type_ids' => ['nullable', 'array'],
            'fee_type_ids.*' => ['integer', 'distinct', Rule::exists('fee_types', 'id')
                ->where('school_id', $school?->id)
                ->where('academic_session_id', $session?->id)
                ->where('invoice_on_admission', true)],
        ]);

        $feeTypeIds = array_map('intval', $request->input('fee_type_ids', []));
        $feeTypes = FeeType::with('period')
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->where('invoice_on_admission', true)
            ->where(function ($query) use ($request): void {
                $query->whereIn('student_type', ['all', 'new_admission']);
                if ($request->filled('student_type_id')) {
                    $query->orWhere(fn ($customTypeQuery) => $customTypeQuery
                        ->where('student_type', 'custom')
                        ->where('student_type_id', $request->integer('student_type_id')));
                }
            })
            ->whereHas('classes', fn ($query) => $query->whereKey($request->integer('school_class_id')))
            ->whereIn('id', $feeTypeIds)
            ->get();
        abort_if($feeTypes->count() !== count($feeTypeIds), 422, 'Selected fees must be assigned to this class and session.');

        $data = $request->except(['_token', 'subject_ids', 'photo']);
        $data['school_id'] = $school?->id;
        $data['academic_session_id'] = $session?->id;
        $data['admission_no'] = $request->admission_no ?: Student::generateAdmissionNo($school?->id ?? 0);

        // Photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student = DB::transaction(function () use ($data, $request, $school, $session, $feeTypes): Student {
            $student = Student::create($data);

            if ($request->filled('subject_ids')) {
                foreach ($request->subject_ids as $subjectId) {
                    $student->subjects()->attach($subjectId, ['is_removed' => false]);
                }
            }

            $registrationInvoiceType = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'registration_invoice_type', 'Combined Invoice');

            $filteredFeeTypes = $feeTypes->filter(function ($feeType) {
                $periodName = strtolower($feeType->period->name ?? '');
                return str_contains($periodName, 'month') || str_contains($periodName, 'one time') || str_contains($periodName, 'onetime');
            });

            if ($registrationInvoiceType === 'Combined Invoice') {
                $totalAmount = $filteredFeeTypes->sum('amount');
                if ($totalAmount > 0) {
                    FeeInvoice::create([
                        'school_id' => $school?->id,
                        'academic_session_id' => $session?->id,
                        'student_id' => $student->id,
                        'fee_type_id' => null,
                        'invoice_title' => 'Admission Fees',
                        'allow_partial_payment' => true,
                        'total_amount' => $totalAmount,
                        'amount_paid' => 0,
                        'due_amount' => $totalAmount,
                        'issued_at' => now()->toDateString(),
                        'status' => 'unpaid',
                    ]);
                }
            } else {
                foreach ($filteredFeeTypes as $feeType) {
                    FeeInvoice::create([
                        'school_id' => $school?->id,
                        'academic_session_id' => $session?->id,
                        'student_id' => $student->id,
                        'fee_type_id' => $feeType->id,
                        'invoice_title' => $feeType->name,
                        'allow_partial_payment' => false,
                        'total_amount' => $feeType->amount,
                        'amount_paid' => 0,
                        'due_amount' => $feeType->amount,
                        'issued_at' => now()->toDateString(),
                        'status' => 'unpaid',
                    ]);
                }
            }

            return $student;
        });

        if (\App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'student_admission_email_enable') == '1' && $student->email) {
            $subject = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'student_admission_email_subject', 'Admission Successful');
            $body = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'student_admission_email_body', 'Welcome [STUDENT_NAME] to [SCHOOL_NAME]!');
            
            $replacements = [
                '[STUDENT_NAME]' => $student->full_name,
                '[CLASS]' => $student->schoolClass->name ?? '',
                '[SECTION]' => $student->section->name ?? '',
                '[ROLL_NUMBER]' => $student->roll_no ?? '',
                '[ENROLLMENT_NUMBER]' => $student->enrollment_no ?? '',
                '[ADMISSION_NUMBER]' => $student->admission_no ?? '',
                '[SCHOOL_NAME]' => $school?->name ?? '',
            ];
            $body = str_replace(array_keys($replacements), array_values($replacements), $body);
            
            $carrier = \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'email_carrier');
            if ($carrier === 'smtp') {
                config(['mail.default' => 'smtp']);
                config(['mail.mailers.smtp.host' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_host')]);
                config(['mail.mailers.smtp.port' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_port')]);
                config(['mail.mailers.smtp.encryption' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_encryption')]);
                config(['mail.mailers.smtp.username' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_username')]);
                config(['mail.mailers.smtp.password' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_password')]);
                config(['mail.from.address' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'smtp_username')]);
                config(['mail.from.name' => \App\Models\SchoolSetting::getSetting($school?->id, $session?->id, 'mail_from_name')]);
                
                app('mail.manager')->purge('smtp');
                
                try {
                    \Illuminate\Support\Facades\Mail::mailer('smtp')->html(nl2br(e($body)), function ($message) use ($student, $subject) {
                        $message->to($student->email)
                                ->subject($subject);
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Admission email failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('sm-student.students.index')
            ->with('success', "Student {$student->full_name} admitted successfully.");
    }

    /** Show edit form */
    public function edit(Student $student): View
    {
        $school = $this->currentSchool();
        $session = $student->academic_session_id ? AcademicSession::find($student->academic_session_id) : $this->currentSession();
        $classes = ($school && $session)
            ? SchoolClass::where('school_id', $school->id)->where('academic_session_id', $session->id)->orderBy('name')->get()
            : collect();
        $sections = $student->school_class_id
            ? Section::where('school_class_id', $student->school_class_id)->get()
            : collect();
        $subjects = $student->school_class_id
            ? Subject::where('school_class_id', $student->school_class_id)->where('is_active', true)->get()
            : collect();
        $categories = StudentCategory::where('school_id', $student->school_id)->orderBy('name')->pluck('name');
        $studentTypes = StudentType::where('school_id', $student->school_id)->orderBy('name')->get();
        $mediums = \App\Models\Medium::where('school_id', $student->school_id)->where('is_active', true)->orderBy('name')->get();

        $assignedSubjectIds = $student->subjects()->pluck('subject_id')->toArray();

        return view('student.students.edit', compact(
            'student', 'classes', 'sections', 'subjects', 'assignedSubjectIds', 'categories', 'studentTypes', 'mediums'
        ));
    }

    /** Update student */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'school_class_id' => 'required|exists:school_classes,id',
            'category' => ['nullable', 'string', Rule::exists('student_categories', 'name')->where('school_id', $student->school_id)],
            'student_type_id' => ['nullable', 'integer', Rule::exists('student_types', 'id')->where('school_id', $student->school_id)],
            'medium_id' => ['nullable', 'integer', Rule::exists('mediums', 'id')->where('school_id', $student->school_id)],
        ]);

        $data = $request->except(['_token', '_method', 'subject_ids', 'photo']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($data);

        // Sync subjects
        if ($request->has('subject_ids')) {
            $syncData = [];
            foreach ($request->subject_ids as $id) {
                $syncData[$id] = ['is_removed' => false];
            }
            $student->subjects()->sync($syncData);
        }

        return redirect()->route('sm-student.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /** Update student status */
    public function updateStatus(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:Active,Inactive,Promoted,Transferred',
        ]);
        $student->update(['status' => $request->status]);

        return back()->with('success', 'Student status updated successfully.');
    }

    /** Delete student */
    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('sm-student.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /** AJAX: Get sections for a class */
    public function getSections(SchoolClass $schoolClass)
    {
        return response()->json($schoolClass->sections()->select('id', 'name')->get());
    }

    /** AJAX: Get subjects for a class */
    public function getSubjects(SchoolClass $schoolClass)
    {
        return response()->json(
            Subject::where('school_class_id', $schoolClass->id)
                ->where('is_active', true)
                ->select('id', 'name', 'code')
                ->get()
        );
    }

    public function export()
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();

        $students = Student::with(['schoolClass', 'section'])
            ->where('school_id', $school?->id)
            ->where('academic_session_id', $session?->id)
            ->orderBy('first_name')
            ->get();

        $filename = "students_export_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Admission No', 'Roll Number', 'First Name', 'Middle Name', 'Last Name', 
            'Class Name', 'Section Name', 'Gender', 'Date of Birth', 'Religion', 'Caste', 
            'Blood Group', 'Phone', 'Email', 'Address', 'City', 'State', 'Country', 
            'Father Name', 'Father Phone', 'Father Occupation', 'Mother Name', 'Mother Phone', 
            'Mother Occupation', 'Guardian Name', 'Guardian Phone', 'Guardian Relation', 
            'Guardian Address', 'Previous School Name', 'Previous Class', 'Previous School Address', 
            'Previous School Board', 'Leaving Certificate No', 'Status', 'Category', 'Extra Note'
        ];

        $callback = function() use($students, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($students as $student) {
                fputcsv($file, [
                    $student->admission_no,
                    $student->roll_number,
                    $student->first_name,
                    $student->middle_name,
                    $student->last_name,
                    $student->schoolClass ? $student->schoolClass->name : '',
                    $student->section ? $student->section->name : '',
                    $student->gender,
                    $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '',
                    $student->religion,
                    $student->caste,
                    $student->blood_group,
                    $student->phone,
                    $student->email,
                    $student->address,
                    $student->city,
                    $student->state,
                    $student->country,
                    $student->father_name,
                    $student->father_phone,
                    $student->father_occupation,
                    $student->mother_name,
                    $student->mother_phone,
                    $student->mother_occupation,
                    $student->guardian_name,
                    $student->guardian_phone,
                    $student->guardian_relation,
                    $student->guardian_address,
                    $student->previous_school_name,
                    $student->previous_class,
                    $student->previous_school_address,
                    $student->previous_school_board,
                    $student->leaving_certificate_no,
                    $student->status,
                    $student->category,
                    $student->extra_note,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importDemo()
    {
        $filename = "students_import_sample.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Admission No', 'Roll Number', 'First Name', 'Middle Name', 'Last Name', 
            'Class Name', 'Section Name', 'Gender', 'Date of Birth', 'Religion', 'Caste', 
            'Blood Group', 'Phone', 'Email', 'Address', 'City', 'State', 'Country', 
            'Father Name', 'Father Phone', 'Father Occupation', 'Mother Name', 'Mother Phone', 
            'Mother Occupation', 'Guardian Name', 'Guardian Phone', 'Guardian Relation', 
            'Guardian Address', 'Previous School Name', 'Previous Class', 'Previous School Address', 
            'Previous School Board', 'Leaving Certificate No', 'Status', 'Category', 'Extra Note'
        ];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'ADM00001', '101', 'John', 'William', 'Doe', 
                'Class 1', 'A', 'Male', '2015-05-14', 'Christian', 'General', 
                'O+', '1234567890', 'john@example.com', '123 Main St', 'New York', 'NY', 'USA', 
                'Richard Doe', '1234567891', 'Engineer', 'Jane Doe', '1234567892', 
                'Teacher', 'Richard Doe', '1234567891', 'Father', 
                '123 Main St', 'Previous Academy', 'KG', '456 Old St', 
                'State Board', 'LC12345', 'Active', 'General', 'Needs extra care in math'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $school = $this->currentSchool();
        $session = $this->currentSession();
        
        $request->validate([
            'csv_file' => 'required|file|extensions:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');

        $header = fgetcsv($handle);
        
        $importedCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7 || empty($row[2])) continue; 
            
            $admissionNo = trim($row[0] ?? '');
            $rollNumber = trim($row[1] ?? '');
            $firstName = trim($row[2] ?? '');
            $middleName = trim($row[3] ?? '');
            $lastName = trim($row[4] ?? '');
            $className = trim($row[5] ?? '');
            $sectionName = trim($row[6] ?? '');
            
            $gender = trim($row[7] ?? '');
            $dob = trim($row[8] ?? '');
            $religion = trim($row[9] ?? '');
            $caste = trim($row[10] ?? '');
            $bloodGroup = trim($row[11] ?? '');
            $phone = trim($row[12] ?? '');
            $email = trim($row[13] ?? '');
            $address = trim($row[14] ?? '');
            $city = trim($row[15] ?? '');
            $state = trim($row[16] ?? '');
            $country = trim($row[17] ?? '');
            
            $fatherName = trim($row[18] ?? '');
            $fatherPhone = trim($row[19] ?? '');
            $fatherOcc = trim($row[20] ?? '');
            $motherName = trim($row[21] ?? '');
            $motherPhone = trim($row[22] ?? '');
            $motherOcc = trim($row[23] ?? '');
            $guardianName = trim($row[24] ?? '');
            $guardianPhone = trim($row[25] ?? '');
            $guardianRelation = trim($row[26] ?? '');
            $guardianAddress = trim($row[27] ?? '');
            
            $prevSchool = trim($row[28] ?? '');
            $prevClass = trim($row[29] ?? '');
            $prevAddress = trim($row[30] ?? '');
            $prevBoard = trim($row[31] ?? '');
            $leavingCert = trim($row[32] ?? '');
            
            $status = trim($row[33] ?? 'Active');
            $category = trim($row[34] ?? '');
            $extraNote = trim($row[35] ?? '');

            $classId = null;
            $sectionId = null;

            if (!empty($className)) {
                $class = SchoolClass::where('school_id', $school?->id)
                    ->where('academic_session_id', $session?->id)
                    ->where('name', $className)
                    ->first();
                
                if ($class) {
                    $classId = $class->id;
                    if (!empty($sectionName)) {
                        $section = Section::where('school_class_id', $classId)
                            ->where('name', $sectionName)
                            ->first();
                        if ($section) {
                            $sectionId = $section->id;
                        }
                    }
                }
            }

            if (!$classId) continue; 

            if (empty($admissionNo)) {
                $admissionNo = Student::generateAdmissionNo($school?->id ?? 0);
            }

            Student::updateOrCreate([
                'school_id' => $school?->id,
                'academic_session_id' => $session?->id,
                'admission_no' => $admissionNo,
            ], [
                'roll_number' => $rollNumber,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'school_class_id' => $classId,
                'section_id' => $sectionId,
                'gender' => in_array($gender, ['Male', 'Female', 'Other']) ? $gender : 'Male',
                'date_of_birth' => $dob ?: null,
                'religion' => $religion,
                'caste' => $caste,
                'blood_group' => $bloodGroup,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'father_name' => $fatherName,
                'father_phone' => $fatherPhone,
                'father_occupation' => $fatherOcc,
                'mother_name' => $motherName,
                'mother_phone' => $motherPhone,
                'mother_occupation' => $motherOcc,
                'guardian_name' => $guardianName,
                'guardian_phone' => $guardianPhone,
                'guardian_relation' => $guardianRelation,
                'guardian_address' => $guardianAddress,
                'previous_school_name' => $prevSchool,
                'previous_class' => $prevClass,
                'previous_school_address' => $prevAddress,
                'previous_school_board' => $prevBoard,
                'leaving_certificate_no' => $leavingCert,
                'status' => in_array($status, ['Active', 'Inactive', 'Promoted', 'Transferred']) ? $status : 'Active',
                'category' => $category,
                'extra_note' => $extraNote,
            ]);
            
            $importedCount++;
        }
        fclose($handle);

        return redirect()->route('sm-student.students.index')->with('success', "$importedCount students imported successfully.");
    }
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = \App\Models\Student::whereIn('id', $ids)->delete();
        return back()->with('success', "$deleted record(s) deleted successfully.");
    }
}