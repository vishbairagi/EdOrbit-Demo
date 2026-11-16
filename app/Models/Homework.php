<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Homework extends Model
{
    use HasFactory;

    protected $table = 'homeworks';

    protected $fillable = [
        'school_id',
        'academic_session_id',
        'school_class_id',
        'section_id',
        'subject_id',
        'title',
        'description',
        'homework_date',
        'due_date',
        'attachment_url',
        'attachments',
        'added_by',
        'created_by_user_id',
        'created_by_guard',
        'status',
    ];

    protected $casts = [
        'homework_date' => 'date',
        'due_date' => 'date',
        'attachments' => 'array',
    ];

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
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function getAttachmentsListAttribute(): array
    {
        return is_array($this->attachments) ? $this->attachments : [];
    }

    public function getAttachmentsCountAttribute(): int
    {
        return count($this->attachments_list);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->endOfDay()->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
    }
}
