<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['school_id', 'academic_session_id', 'class_group_id', 'name', 'order'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /** @return BelongsToMany<FeeType> */
    public function feeTypes(): BelongsToMany
    {
        return $this->belongsToMany(FeeType::class, 'fee_type_school_class');
    }

    /** @return HasMany<Student> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
