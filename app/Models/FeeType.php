<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'fee_period_id',
        'name',
        'student_type',
        'student_type_id',
        'amount',
        'invoice_on_admission',
        'include_on_promotion',
        'dashboard_disabled',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'invoice_on_admission' => 'boolean',
            'include_on_promotion' => 'boolean',
            'dashboard_disabled' => 'boolean',
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

    /** @return BelongsTo<FeePeriod, self> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(FeePeriod::class, 'fee_period_id');
    }

    /** @return BelongsTo<StudentType, self> */
    public function studentType(): BelongsTo
    {
        return $this->belongsTo(StudentType::class);
    }

    /** @return BelongsToMany<SchoolClass> */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'fee_type_school_class');
    }

    /** @return BelongsToMany<Section> */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'fee_type_section');
    }

    /** @return HasMany<FeeInvoice> */
    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }
}
