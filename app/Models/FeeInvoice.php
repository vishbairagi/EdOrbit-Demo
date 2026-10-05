<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'student_id',
        'fee_type_id',
        'invoice_title',
        'description',
        'allow_partial_payment',
        'total_amount',
        'amount_paid',
        'due_amount',
        'issued_at',
        'due_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'allow_partial_payment' => 'boolean',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
        ];
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

    /** @return BelongsTo<Student, self> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<FeeType, self> */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /** @return HasMany<FeePayment> */
    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }
}
