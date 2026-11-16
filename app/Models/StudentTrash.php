<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StudentTrash extends Model
{
    protected $table = 'student_trashes';

    protected $fillable = [
        'school_id',
        'academic_session_id',
        'student_id',
        'admission_no',
        'first_name',
        'last_name',
        'full_name',
        'school_class_name',
        'section_name',
        'photo',
        'gender',
        'phone',
        'student_data',
        'related_data',
        'related_counts',
        'deleted_at',
    ];

    protected $casts = [
        'student_data' => 'array',
        'related_data' => 'array',
        'related_counts' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function getDaysRemainingAttribute(): int
    {
        $deleted = $this->deleted_at ?? $this->created_at ?? now();
        $diff = Carbon::parse($deleted)->diffInDays(now());
        return max(0, 30 - (int) $diff);
    }

    public function getExpiresAtAttribute(): Carbon
    {
        $deleted = $this->deleted_at ?? $this->created_at ?? now();
        return Carbon::parse($deleted)->addDays(30);
    }

    /**
     * Archive student and all related records to trash, then remove from active tables.
     */
    public static function moveToTrash(Student $student): self
    {
        // 1. Gather all related data
        $userLogin = DB::table('users')->where('student_id', $student->id)->first();
        $subjects = DB::table('student_subjects')->where('student_id', $student->id)->get();
        
        $feeInvoices = FeeInvoice::with(['payments'])->where('student_id', $student->id)->get();
        $transportInvoices = TransportInvoice::where('student_id', $student->id)->get();
        $transportRoutes = DB::table('student_transport_route')->where('student_id', $student->id)->get();
        
        $libraryCards = LibraryCard::where('student_id', $student->id)->get();
        $bookIssues = LibraryBookIssue::where('student_id', $student->id)->get();
        $leaves = StudentLeave::where('student_id', $student->id)->get();
        
        $admitCards = ExamAdmitCard::where('student_id', $student->id)->get();
        $examResults = ExamResult::with('items')->where('student_id', $student->id)->get();

        // 2. Create Trash record
        $trash = static::create([
            'school_id' => $student->school_id,
            'academic_session_id' => $student->academic_session_id,
            'student_id' => $student->id,
            'admission_no' => $student->admission_no,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'school_class_name' => $student->schoolClass?->name,
            'section_name' => $student->section?->name,
            'photo' => $student->photo,
            'gender' => $student->gender,
            'phone' => $student->phone,
            'student_data' => $student->getAttributes(),
            'related_data' => [
                'user_login' => $userLogin ? (array) $userLogin : null,
                'subjects' => $subjects->toArray(),
                'fee_invoices' => $feeInvoices->map(fn ($inv) => [
                    'invoice' => $inv->getAttributes(),
                    'payments' => $inv->payments ? $inv->payments->map->getAttributes()->all() : [],
                ])->all(),
                'transport_invoices' => $transportInvoices->map->getAttributes()->all(),
                'transport_routes' => $transportRoutes->toArray(),
                'library_cards' => $libraryCards->map->getAttributes()->all(),
                'book_issues' => $bookIssues->map->getAttributes()->all(),
                'leaves' => $leaves->map->getAttributes()->all(),
                'admit_cards' => $admitCards->map->getAttributes()->all(),
                'exam_results' => $examResults->map(fn ($res) => [
                    'result' => $res->getAttributes(),
                    'items' => $res->items ? $res->items->map->getAttributes()->all() : [],
                ])->all(),
            ],
            'related_counts' => [
                'invoices' => $feeInvoices->count(),
                'transport' => $transportInvoices->count(),
                'library_cards' => $libraryCards->count(),
                'book_issues' => $bookIssues->count(),
                'leaves' => $leaves->count(),
                'admit_cards' => $admitCards->count(),
                'exam_results' => $examResults->count(),
                'has_login' => !empty($userLogin) ? 1 : 0,
            ],
            'deleted_at' => now(),
        ]);

        // 3. Wipe from active tables completely
        DB::table('users')->where('student_id', $student->id)->delete();
        FeeInvoice::where('student_id', $student->id)->delete();
        TransportInvoice::where('student_id', $student->id)->delete();
        DB::table('student_transport_route')->where('student_id', $student->id)->delete();
        LibraryCard::where('student_id', $student->id)->delete();
        LibraryBookIssue::where('student_id', $student->id)->delete();
        StudentLeave::where('student_id', $student->id)->delete();
        ExamAdmitCard::where('student_id', $student->id)->delete();
        ExamResult::where('student_id', $student->id)->delete();
        DB::table('student_subjects')->where('student_id', $student->id)->delete();
        
        $student->delete();

        return $trash;
    }

    /**
     * Restore student and all archived data back to active database tables.
     */
    public function restoreStudent(): Student
    {
        $studentAttrs = $this->student_data;
        
        // Check if admission_no is already taken by another active student in this session
        $conflict = Student::where('school_id', $this->school_id)
            ->where('academic_session_id', $this->academic_session_id)
            ->where('admission_no', $studentAttrs['admission_no'] ?? null)
            ->exists();

        if ($conflict) {
            $studentAttrs['admission_no'] = Student::generateAdmissionNo($this->school_id);
        }

        // Recreate Student
        $student = new Student();
        foreach ($studentAttrs as $k => $v) {
            $student->setAttribute($k, $v);
        }
        $student->save();

        $newStudentId = $student->id;
        $related = $this->related_data ?? [];

        // 1. Restore User Login
        if (! empty($related['user_login'])) {
            $login = $related['user_login'];
            $username = $login['username'] ?? '';
            $email = $login['email'] ?? '';
            
            $userConflict = DB::table('users')->where('username', $username)->orWhere('email', $email)->exists();
            if ($userConflict) {
                $username = $username . '_recovered';
                $email = 'recovered_' . $email;
            }

            DB::table('users')->insert([
                'name' => $login['name'] ?? $student->full_name,
                'username' => $username,
                'email' => $email,
                'password' => $login['password'] ?? bcrypt('123456'),
                'user_type' => 'student',
                'student_id' => $newStudentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Restore student subjects
        if (! empty($related['subjects'])) {
            foreach ($related['subjects'] as $sub) {
                DB::table('student_subjects')->insertOrIgnore([
                    'student_id' => $newStudentId,
                    'subject_id' => $sub['subject_id'],
                    'is_removed' => $sub['is_removed'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Restore fee invoices
        if (! empty($related['fee_invoices'])) {
            foreach ($related['fee_invoices'] as $invData) {
                $invAttrs = $invData['invoice'] ?? $invData;
                unset($invAttrs['id']);
                $invAttrs['student_id'] = $newStudentId;
                $newInvoice = FeeInvoice::create($invAttrs);

                if (! empty($invData['payments'])) {
                    foreach ($invData['payments'] as $paymentAttrs) {
                        unset($paymentAttrs['id']);
                        $paymentAttrs['fee_invoice_id'] = $newInvoice->id;
                        DB::table('fee_payments')->insert($paymentAttrs);
                    }
                }
            }
        }

        // 4. Restore transport invoices
        if (! empty($related['transport_invoices'])) {
            foreach ($related['transport_invoices'] as $tInv) {
                unset($tInv['id']);
                $tInv['student_id'] = $newStudentId;
                TransportInvoice::create($tInv);
            }
        }

        // 5. Restore transport routes
        if (! empty($related['transport_routes'])) {
            foreach ($related['transport_routes'] as $tr) {
                DB::table('student_transport_route')->insertOrIgnore([
                    'student_id' => $newStudentId,
                    'transport_route_id' => $tr['transport_route_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Restore library cards & books
        if (! empty($related['library_cards'])) {
            foreach ($related['library_cards'] as $lc) {
                unset($lc['id']);
                $lc['student_id'] = $newStudentId;
                LibraryCard::create($lc);
            }
        }
        if (! empty($related['book_issues'])) {
            foreach ($related['book_issues'] as $bi) {
                unset($bi['id']);
                $bi['student_id'] = $newStudentId;
                LibraryBookIssue::create($bi);
            }
        }

        // 7. Restore leaves
        if (! empty($related['leaves'])) {
            foreach ($related['leaves'] as $leave) {
                unset($leave['id']);
                $leave['student_id'] = $newStudentId;
                StudentLeave::create($leave);
            }
        }

        // 8. Restore admit cards & exam results
        if (! empty($related['admit_cards'])) {
            foreach ($related['admit_cards'] as $ac) {
                unset($ac['id']);
                $ac['student_id'] = $newStudentId;
                ExamAdmitCard::create($ac);
            }
        }
        if (! empty($related['exam_results'])) {
            foreach ($related['exam_results'] as $resData) {
                $resAttrs = $resData['result'] ?? $resData;
                unset($resAttrs['id']);
                $resAttrs['student_id'] = $newStudentId;
                $newResult = ExamResult::create($resAttrs);

                if (! empty($resData['items'])) {
                    foreach ($resData['items'] as $itemAttrs) {
                        unset($itemAttrs['id']);
                        $itemAttrs['exam_result_id'] = $newResult->id;
                        DB::table('exam_result_items')->insert($itemAttrs);
                    }
                }
            }
        }

        // Delete from trash
        $this->delete();

        return $student;
    }

    /**
     * Automatically purge trashed records that have exceeded 30 days.
     */
    public static function purgeExpired(): int
    {
        return static::where('deleted_at', '<', now()->subDays(30))->delete();
    }
}
