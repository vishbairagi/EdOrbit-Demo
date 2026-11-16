<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Student extends Model implements JWTSubject
{
    protected $fillable = [
        'admission_no',
        'school_id',
        'academic_session_id',
        'school_class_id',
        'section_id',
        'medium_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'religion',
        'caste',
        'blood_group',
        'address',
        'phone',
        'email',
        'city',
        'state',
        'country',
        'extra_note',
        'category',
        'student_type_id',
        'photo',
        'father_name',
        'father_phone',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'guardian_address',
        'previous_school_name',
        'previous_class',
        'previous_school_address',
        'previous_school_board',
        'leaving_certificate_no',
        'status',
        'roll_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /** Full name accessor */
    public function getFullNameAttribute(): string
    {
        return preg_replace('/\s+/', ' ', trim("{$this->first_name} {$this->middle_name} {$this->last_name}"));
    }

    /** Photo URL accessor */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }

        return asset('storage/'.ltrim($this->photo, '/'));
    }

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
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function medium(): BelongsTo
    {
        return $this->belongsTo(Medium::class);
    }

    /** @return BelongsTo<StudentType, self> */
    public function studentType(): BelongsTo
    {
        return $this->belongsTo(StudentType::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects')
            ->withPivot('is_removed')
            ->withTimestamps();
    }

    public function activeSubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('is_removed', false);
    }

    /** @return HasMany<FeeInvoice> */
    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    /** Generate next admission number for a school */
    public static function generateAdmissionNo(int $schoolId): string
    {
        $count = static::where('school_id', $schoolId)->count() + 1;

        return 'ADM'.str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /** @return HasMany<LibraryCard> */
    public function libraryCards(): HasMany
    {
        return $this->hasMany(LibraryCard::class);
    }

    /** @return HasMany<TransportRoute> */
    public function transportRoutes(): BelongsToMany
    {
        return $this->belongsToMany(TransportRoute::class, 'student_transport_route');
    }

    /** @return HasMany<StudentAttendance> */
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user_type' => 'student',
            'guard' => 'student',
            'student_id' => $this->id,
            'school_id' => $this->school_id,
            'session_id' => $this->academic_session_id,
            'class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'user' => [
                'id' => (string) $this->id,
                'type' => 'student',
            ],
        ];
    }
}
