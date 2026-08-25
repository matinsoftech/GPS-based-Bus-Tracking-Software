<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'driver_id',
        'bus_number',
        'registration_number',
        'make',
        'model',
        'year',
        'capacity',
        'fuel_type',
        'gps_device_id',
        'insurance_number',
        'insurance_expiry_date',
        'last_service_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'insurance_expiry_date' => 'date',
        'last_service_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'bus_route', 'bus_id', 'route_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function gpsDevice()
    {
        return $this->hasOne(GpsDevice::class, 'bus_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function activeTrip()
    {
        return $this->hasOne(Trip::class)->where('status', Trip::STATUS_IN_PROGRESS);
    }
}
