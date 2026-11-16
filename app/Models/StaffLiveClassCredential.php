<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLiveClassCredential extends Model
{
    protected $table = 'staff_live_class_credentials';

    protected $fillable = [
        'school_id',
        'academic_session_id',
        'staff_id',
        'google_client_id',
        'google_client_secret',
        'google_redirect_uri',
        'google_access_token',
        'google_refresh_token',
        'token_expires_at',
        'is_connected',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_connected' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function isConfigured(): bool
    {
        return !empty($this->google_client_id) && !empty($this->google_client_secret);
    }
}
