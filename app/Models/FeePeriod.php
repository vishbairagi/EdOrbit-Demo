<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeePeriod extends Model
{
    protected $fillable = ['school_id', 'academic_session_id', 'name', 'duration_months'];

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

    /** @return HasMany<FeeType> */
    public function feeTypes(): HasMany
    {
        return $this->hasMany(FeeType::class);
    }
}
