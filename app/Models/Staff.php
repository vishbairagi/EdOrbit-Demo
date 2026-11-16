<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Staff extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'staff';

    protected $fillable = [
        'school_id',
        'staff_role_id',
        'employee_id',
        'name',
        'gender',
        'date_of_birth',
        'address',
        'phone',
        'email',
        'photo',
        'designation',
        'joining_date',
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

    /** @return BelongsTo<StaffRole, self> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class, 'staff_role_id');
    }

    /** @return HasMany<StaffAttendance> */
    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /** @return HasMany<StaffLeave> */
    public function leaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class, 'applicant_id')
            ->where('applicant_type', 'staff');
    }

    /** @return HasMany<StaffSalaryRecord> */
    public function salaryRecords(): HasMany
    {
        return $this->hasMany(StaffSalaryRecord::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function hasPermission(string $key): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->permissions()->where('permission_key', $key)->exists();
    }

    /**
     * Generate a unique employee ID for a school.
     */
    public static function generateEmployeeId(int $schoolId): string
    {
        $count = self::where('school_id', $schoolId)->count() + 1;

        return 'EMP-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'staff_subject')->withTimestamps();
    }

    public function classTeachers()
    {
        return $this->hasMany(ClassTeacher::class);
    }

    public function liveClassCredentials()
    {
        return $this->hasMany(StaffLiveClassCredential::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user_type' => 'teacher',
            'guard' => 'staff',
            'school_id' => $this->school_id,
        ];
    }
}
