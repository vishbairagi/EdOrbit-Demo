<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaffLeave extends Model
{
    protected $fillable = [
        'school_id',
        'applicant_id',
        'applicant_type',
        'from_date',
        'to_date',
        'leave_type',
        'reason',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, self> */
    public function applicant(): MorphTo
    {
        return $this->morphTo('applicant', 'applicant_type', 'applicant_id');
    }

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getTotalDaysAttribute(): int
    {
        return $this->from_date->diffInDays($this->to_date) + 1;
    }
}
