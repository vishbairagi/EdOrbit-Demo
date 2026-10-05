<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'school_class_id',
        'exam_group_id',
        'title',
        'exam_center',
        'start_date',
        'end_date',
        'exam_type',
        'grade_criteria',
        'result_thresholds',
        'settings',
        'admit_roll_prefix',
        'admit_roll_start',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'grade_criteria' => 'array',
            'result_thresholds' => 'array',
            'settings' => 'array',
        ];
    }

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

    public function examGroup(): BelongsTo
    {
        return $this->belongsTo(ExamGroup::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'exam_sections');
    }

    public function papers(): HasMany
    {
        return $this->hasMany(ExamPaper::class);
    }

    public function admitCards(): HasMany
    {
        return $this->hasMany(ExamAdmitCard::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
