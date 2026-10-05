<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCategory extends Model
{
    public const DEFAULT_NAMES = ['General', 'OBC', 'SC', 'ST', 'Other'];

    protected $fillable = ['school_id', 'academic_session_id', 'name'];

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<AcademicSession, self> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    /** @return HasMany<Student> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'category', 'name')
            ->whereColumn('students.school_id', 'student_categories.school_id');
    }
}