<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResultItem extends Model
{
    protected $fillable = ['exam_result_id', 'exam_paper_id', 'obtained_marks', 'grade', 'status', 'remark'];

    protected function casts(): array
    {
        return ['obtained_marks' => 'decimal:2'];
    }

    public function examResult(): BelongsTo
    {
        return $this->belongsTo(ExamResult::class);
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paper_id');
    }
}
