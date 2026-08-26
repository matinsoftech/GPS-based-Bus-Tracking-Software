<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const TRIP_HOME_TO_SCHOOL = 'home_to_school';

    public const TRIP_SCHOOL_TO_HOME = 'school_to_home';

    protected $fillable = [
        'student_id',
        'route_id',
        'trip',
        'date',
        'check_in_at',
        'check_out_at',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    /**
     * The two trips taken in a single day.
     */
    public static function trips(): array
    {
        return [
            self::TRIP_HOME_TO_SCHOOL => 'Home to School (Pickup)',
            self::TRIP_SCHOOL_TO_HOME => 'School to Home (Drop)',
        ];
    }

    public function tripLabel(): string
    {
        return self::trips()[$this->trip] ?? $this->trip;
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function isCheckedIn(): bool
    {
        return $this->check_in_at !== null;
    }

    public function isCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }
}
