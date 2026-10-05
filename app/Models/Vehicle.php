<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'vehicle_number',
        'vehicle_model',
        'driver_name',
        'driver_phone',
        'note',
        'license_number',
    ];

    /** @return BelongsTo<School, self> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsToMany<TransportRoute, self> */
    public function transportRoutes(): BelongsToMany
    {
        return $this->belongsToMany(TransportRoute::class, 'transport_route_vehicle');
    }
}
