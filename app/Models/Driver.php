<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'license_number',
        'license_type',
        'license_issue_date',
        'license_expiry_date',
        'experience_years',
        'joining_date',
        'status',
        'profile_photo',
        'emergency_contact_name',
        'emergency_contact_phone',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'joining_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buses()
    {
        return $this->belongsToMany(Bus::class, 'bus_driver', 'driver_id', 'bus_id');
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'driver_route', 'driver_id', 'route_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function activeTrips()
    {
        return $this->trips()->where('status', Trip::STATUS_IN_PROGRESS);
    }

    public function attendances()
    {
        return Attendance::whereIn('route_id', $this->routes()->pluck('driver_route.route_id'));
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public static function nameParts(string $name): array
    {
        [$firstName, $lastName] = array_pad(
            preg_split('/\s+/', trim($name), 2),
            2,
            ''
        );

        return ['first_name' => $firstName, 'last_name' => $lastName];
    }
}
