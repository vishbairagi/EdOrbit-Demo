<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassTimetable extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'school_class_id',
        'section_id',
        'subject_id',
        'staff_id',
        'day',
        'start_time',
        'end_time',
        'room_number',
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
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function getFormattedStartTimeAttribute(): string
    {
        if (empty($this->start_time)) {
            return '';
        }
        $ts = strtotime($this->start_time);
        return $ts ? date('h:i a', $ts) : $this->start_time;
    }

    public function getFormattedEndTimeAttribute(): string
    {
        if (empty($this->end_time)) {
            return '';
        }
        $ts = strtotime($this->end_time);
        return $ts ? date('h:i a', $ts) : $this->end_time;
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = $this->formatted_start_time;
        $end = $this->formatted_end_time;
        if ($start && $end) {
            return $start . ' - ' . $end;
        }
        return $start ?: $end;
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'class_timetable_id');
    }
}
