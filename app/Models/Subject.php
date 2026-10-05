<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'school_class_id',
        'name',
        'code',
        'type',
        'subject_type_id',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(SubjectType::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Staff::class, 'staff_subject')->withTimestamps();
    }
}
