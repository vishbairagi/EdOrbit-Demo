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
}
