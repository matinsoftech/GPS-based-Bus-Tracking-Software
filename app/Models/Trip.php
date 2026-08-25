<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    public const TYPE_HOME_TO_SCHOOL = 'home_to_school';
    public const TYPE_SCHOOL_TO_HOME = 'school_to_home';

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'bus_id',
        'driver_id',
        'school_id',
        'trip_type',
        'status',
        'started_at',
        'ended_at',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'start_latitude' => 'decimal:7',
        'start_longitude' => 'decimal:7',
        'end_latitude' => 'decimal:7',
        'end_longitude' => 'decimal:7',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function duration(): ?string
    {
        if (! $this->started_at) {
            return null;
        }

        $end = $this->ended_at ?? now();

        return $this->started_at->diffForHumans($end, ['parts' => 3]);
    }

    public function durationInMinutes(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        $end = $this->ended_at ?? now();

        return (int) $this->started_at->diffInMinutes($end);
    }

    public static function types(): array
    {
        return [
            self::TYPE_HOME_TO_SCHOOL => 'Home to School',
            self::TYPE_SCHOOL_TO_HOME => 'School to Home',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public static function tripTypeByTime(?string $time = null): string
    {
        $hour = ($time ? \Carbon\Carbon::parse($time) : now())->hour;

        return $hour < 12
            ? self::TYPE_HOME_TO_SCHOOL
            : self::TYPE_SCHOOL_TO_HOME;
    }
}
