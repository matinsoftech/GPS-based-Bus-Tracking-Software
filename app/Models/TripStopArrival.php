<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStopArrival extends Model
{
    public const STATUS_ARRIVED = 'arrived';

    public const STATUS_DEPARTED = 'departed';

    protected $fillable = [
        'trip_id',
        'route_stop_id',
        'bus_id',
        'status',
        'arrived_at',
        'departed_at',
        'schedule_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'departed_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function isArrived(): bool
    {
        return $this->status === self::STATUS_ARRIVED;
    }

    /**
     * Whether the bus reached this stop before, at, or after its scheduled time.
     *
     * @return 'early'|'on_time'|'late'|null
     */
    public function getPunctualityAttribute(): ?string
    {
        if (! $this->arrived_at || ! $this->schedule_at) {
            return null;
        }

        $schedule = Carbon::today()->setTimeFromTimeString($this->schedule_at);

        if ($this->arrived_at->lt($schedule->copy()->subMinutes(1))) {
            return 'early';
        }

        if ($this->arrived_at->gt($schedule->copy()->addMinutes(1))) {
            return 'late';
        }

        return 'on_time';
    }

    /**
     * Whole minutes the arrival was late (negative when early, 0 when on time).
     */
    public function getLatenessMinutesAttribute(): int
    {
        if (! $this->arrived_at || ! $this->schedule_at) {
            return 0;
        }

        $schedule = Carbon::today()->setTimeFromTimeString($this->schedule_at);

        return (int) round($this->arrived_at->diffInMinutes($schedule, false));
    }
}
