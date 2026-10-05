<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPaper extends Model
{
    protected $fillable = [
        'exam_id',
        'subject_id',
        'subject_name',
        'subject_type',
        'maximum_marks',
        'paper_code',
        'paper_date',
        'start_time',
        'end_time',
        'room_number',
    ];

    protected function casts(): array
    {
        return [
            'maximum_marks' => 'decimal:2',
            'paper_date' => 'date',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function resultItems(): HasMany
    {
        return $this->hasMany(ExamResultItem::class);
    }
}
