<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffRole extends Model
{
    protected $fillable = ['school_id', 'name', 'description', 'status'];

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return HasMany<RolePermission> */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /** @return HasMany<Staff> */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
