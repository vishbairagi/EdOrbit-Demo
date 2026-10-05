<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryBookIssue extends Model
{
    protected $fillable = [
        'school_id', 'academic_session_id', 'book_id', 'student_id',
        'issue_date', 'return_due_date',
        'actual_return_date', 'return_note', 'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'return_due_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
