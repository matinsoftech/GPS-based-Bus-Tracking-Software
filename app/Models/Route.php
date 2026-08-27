<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'route_code',
        'start_location',
        'end_location',
        'estimated_distance',
        'estimated_duration',
        'is_active',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'driver_route', 'route_id', 'driver_id');
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class)
            ->orderBy('stop_order');
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
