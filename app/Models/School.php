<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'phone',
        'email',
        'address',
        'description',
        'registration_number',
        'status',
        'enrollment_prefix',
        'enrollment_base_number',
        'enrollment_base_padding',
        'admission_prefix',
        'admission_base_number',
        'admission_base_padding',
    ];

    /**
     * Get the full URL to the school's logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        if (str_starts_with($this->logo, 'uploads/')) {
            return asset($this->logo);
        }

        return asset('storage/' . ltrim($this->logo, '/'));
    }

    /** @return HasMany<StudentCategory> */
    public function studentCategories(): HasMany
    {
        return $this->hasMany(StudentCategory::class);
    }

    /** @return HasMany<StudentType> */
    public function studentTypes(): HasMany
    {
        return $this->hasMany(StudentType::class);
    }

    /** @return HasMany<FeePeriod> */
    public function feePeriods(): HasMany
    {
        return $this->hasMany(FeePeriod::class);
    }

    /** @return HasMany<PaymentMethod> */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /** @return HasMany<FeeType> */
    public function feeTypes(): HasMany
    {
        return $this->hasMany(FeeType::class);
    }

    /** @return HasMany<FeeInvoice> */
    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    /** @return HasMany<Student> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /** @return HasMany<SchoolClass> */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /** @return HasMany<Staff> */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
