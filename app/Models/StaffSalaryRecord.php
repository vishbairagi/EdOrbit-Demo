<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSalaryRecord extends Model
{
    protected $fillable = [
        'school_id',
        'staff_id',
        'academic_session_id',
        'month',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'note',
        'details',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'details' => 'array',
        ];
    }

    /** @return BelongsTo<Staff, self> */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<AcademicSession, self> */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }
}
