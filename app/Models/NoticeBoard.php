<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NoticeBoard extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'school_class_id',
        'section_id',
        'title',
        'description',
        'link_type', // 'none', 'attachment', 'url'
        'link_url',
        'attachment_path',
        'attachment_original_name',
        'attachment_size',
        'is_active',
        'status',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        if (str_starts_with($this->attachment_path, 'http')) {
            return $this->attachment_path;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getAttachmentExtensionAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION));
    }
}
