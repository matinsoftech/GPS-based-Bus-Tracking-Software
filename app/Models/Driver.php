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
        return $this->hasMany(Bus::class);
    }

    public function attendances()
    {
        return $this->hasManyThrough(
            Attendance::class,
            Bus::class,
            'driver_id', // FK on buses
            'bus_id',    // FK on attendances
            'id',        // local key on drivers
            'id',        // local key on buses
        );
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
