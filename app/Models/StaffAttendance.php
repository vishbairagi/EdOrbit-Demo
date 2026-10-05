<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    protected $fillable = ['school_id', 'staff_id', 'date', 'status', 'note'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    /** @return BelongsTo<Staff, self> */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
