<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransportRoute extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'route_name',
        'route_fare',
        'period_id',
    ];

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<FeePeriod, self> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(FeePeriod::class, 'period_id');
    }

    /** @return BelongsToMany<Vehicle, self> */
    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'transport_route_vehicle');
    }

    /** @return BelongsToMany<Student, self> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_transport_route');
    }
}
