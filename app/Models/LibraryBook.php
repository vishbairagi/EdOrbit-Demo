<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    protected $fillable = [
        'school_id', 'academic_session_id', 'book_type', 'class_ids', 'subject_ids',
        'title', 'author', 'subject',
        'rack_number', 'book_number', 'isbn_number',
        'price', 'quantity', 'description',
    ];

    protected $casts = [
        'class_ids' => 'array',
        'subject_ids' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(LibraryBookIssue::class, 'book_id');
    }

    public function activeIssues(): HasMany
    {
        return $this->issues()->where('status', 'issued');
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->quantity - $this->activeIssues()->count());
    }
}
