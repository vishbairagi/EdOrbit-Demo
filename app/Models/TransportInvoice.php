<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportInvoice extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'student_id',
        'transport_route_id',
        'invoice_title',
        'issued_at',
        'start_date',
        'end_date',
        'total_amount',
        'amount_paid',
        'due_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'issued_at' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
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

    /** @return BelongsTo<TransportRoute, self> */
    public function transportRoute(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class);
    }

    /** @return HasMany<TransportPayment> */
    public function payments(): HasMany
    {
        return $this->hasMany(TransportPayment::class);
    }
}
