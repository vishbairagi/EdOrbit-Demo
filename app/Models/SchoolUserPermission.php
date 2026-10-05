<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolUserPermission extends Model
{
    protected $fillable = ['school_user_id', 'permission_key'];

    /** @return BelongsTo<SchoolUser, self> */
    public function schoolUser(): BelongsTo
    {
        return $this->belongsTo(SchoolUser::class);
    }
}
