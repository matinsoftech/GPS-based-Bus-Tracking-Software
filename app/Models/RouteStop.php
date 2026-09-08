<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    protected $fillable = [
        'route_id',
        'name',
        'latitude',
        'longitude',
        'stop_order',
        'pickup_time',
        'drop_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'route_stop_student', 'route_stop_id', 'student_id');
    }
}
