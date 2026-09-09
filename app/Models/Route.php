<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'route_code',
        'route_type',
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

    public function getRouteTypeLabelAttribute(): string
    {
        return $this->route_type === 'school_to_home' ? 'School to Home' : 'Home to School';
    }

    public function getRouteTypeColorClassesAttribute(): string
    {
        return $this->route_type === 'school_to_home'
            ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400'
            : 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400';
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
        return $this->belongsToMany(Student::class, 'route_student');
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
