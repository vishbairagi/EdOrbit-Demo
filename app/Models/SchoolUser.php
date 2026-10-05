<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SchoolUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'school_users';

    protected $fillable = [
        'school_id',
        'employee_id',
        'name',
        'gender',
        'date_of_birth',
        'address',
        'phone',
        'email',
        'photo',
        'joining_date',
        'role',
        'note',
        'govt_documents',
        'salary',
        'salary_type',
        'salary_details',
        'username',
        'login_email',
        'login_phone',
        'password',
        'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'govt_documents' => 'array',
            'salary_details' => 'array',
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return HasMany<SchoolUserPermission> */
    public function permissions(): HasMany
    {
        return $this->hasMany(SchoolUserPermission::class);
    }

    /** @return HasMany<StaffLeave> */
    public function leaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class, 'applicant_id')
            ->where('applicant_type', 'school_user');
    }

    public function hasPermission(string $key): bool
    {
        return $this->permissions()->where('permission_key', $key)->exists();
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Generate a unique employee ID for a school.
     */
    public static function generateEmployeeId(int $schoolId): string
    {
        $count = self::where('school_id', $schoolId)->count() + 1;

        return 'ADM-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
