<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamGroup extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'name',
        'grade_criteria',
        'result_thresholds',
    ];

    protected function casts(): array
    {
        return [
            'grade_criteria' => 'array',
            'result_thresholds' => 'array',
        ];
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
