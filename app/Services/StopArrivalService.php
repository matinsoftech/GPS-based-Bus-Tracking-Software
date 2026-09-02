<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\RouteStop;
use App\Models\Student;
use App\Models\Trip;
use App\Models\TripStopArrival;
use App\Notifications\BusStopArrivalNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StopArrivalService
{
    /** A live bus is considered stopped when speed is at or below this (km/h). */
    public const STOPPED_SPEED_KPH = 3;

    /** Distance (km) within which a stopped bus is considered "Arrived" at a stop. */
    public const ARRIVED_RADIUS_KM = 0.25;

    /**
     * Given a bus fix, detect whether the bus has arrived at (or left) a stop on
     * its active trip's route, persist the change, and notify the affected parents.
     */
    public function detectForBus(Bus $bus, float $latitude, float $longitude, float $speedKmh): void
    {
        try {
            $trip = Trip::where('bus_id', $bus->id)
                ->where('status', Trip::STATUS_IN_PROGRESS)
                ->with(['route.stops', 'driver'])
                ->latest('started_at')
                ->first();

            if (! $trip || ! $trip->route) {
                return;
            }

            $stops = $trip->route->stops
                ?? $trip->route->stops()->where('is_active', true)->get();

            if ($stops->isEmpty()) {
                return;
            }

            $this->process($bus, $trip, $stops, $latitude, $longitude, $speedKmh);
        } catch (\Throwable $e) {
            Log::error('Stop arrival detection failed', [
                'bus_id' => $bus->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Compute the nearest stop to the given fix, or null when there are none.
     *
     * @param  Collection<int, RouteStop>  $stops
     * @return array{stop: RouteStop, distance_km: float}|null
     */
    protected function nearestStop(array|Collection $stops, float $lat, float $lng): ?array
    {
        $nearest = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($stops as $stop) {
            if (! is_numeric($stop->latitude) || ! is_numeric($stop->longitude)) {
                continue;
            }

            $distance = $this->haversineKm($lat, $lng, (float) $stop->latitude, (float) $stop->longitude);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $stop;
            }
        }

        if (! $nearest) {
            return null;
        }

        return ['stop' => $nearest, 'distance_km' => $nearestDistance];
    }

    /**
     * Persist an arrival/departure for the given stop and trip.
     */
    protected function process(
        Bus $bus,
        Trip $trip,
        $stops,
        float $latitude,
        float $longitude,
        float $speedKmh,
    ): void {
        $nearest = $this->nearestStop($stops, $latitude, $longitude);

        if (! $nearest) {
            return;
        }

        [$stop, $distance] = [$nearest['stop'], $nearest['distance_km']];

        $isStopped = $speedKmh <= self::STOPPED_SPEED_KPH;
        $isAtStop = $distance <= self::ARRIVED_RADIUS_KM;

        $arrival = TripStopArrival::where('trip_id', $trip->id)
            ->where('route_stop_id', $stop->id)
            ->first();

        if ($isAtStop && $isStopped) {
            if ($arrival && $arrival->isArrived()) {
                return;
            }

            $created = TripStopArrival::updateOrCreate(
                ['trip_id' => $trip->id, 'route_stop_id' => $stop->id],
                [
                    'bus_id' => $bus->id,
                    'status' => TripStopArrival::STATUS_ARRIVED,
                    'arrived_at' => now(),
                    'departed_at' => null,
                    'schedule_at' => $this->scheduleFor($trip, $stop),
                ]
            );

            $this->notifyParents($trip, $stop, $created);
        } elseif ($arrival && $arrival->isArrived() && (! $isAtStop || ! $isStopped)) {
            $arrival->update([
                'status' => TripStopArrival::STATUS_DEPARTED,
                'departed_at' => now(),
            ]);
        }
    }

    /**
     * The scheduled time for a stop, based on the trip direction.
     */
    protected function scheduleFor(Trip $trip, RouteStop $stop): ?string
    {
        return $trip->trip_type === Trip::TYPE_HOME_TO_SCHOOL ? $stop->pickup_time : $stop->drop_time;
    }

    /**
     * Notify the parents (and students) of students whose route includes this stop.
     */
    protected function notifyParents(Trip $trip, RouteStop $stop, TripStopArrival $arrival): void
    {
        $students = Student::where('route_id', $trip->route_id)
            ->with('parent.user')
            ->get();

        $notification = new BusStopArrivalNotification($trip, $arrival, $stop);

        foreach ($students as $student) {
            if ($parent = $student->parent?->user) {
                $parent->notify($notification);
            }
            if ($studentUser = $student->user) {
                $studentUser->notify($notification);
            }
        }
    }

    /**
     * Enrich a normalized GPS payload with authoritative stop-arrival context for
     * the parent tracking page.
     *
     * @param  array|null  $location  A payload from NazarTrackService::locationPayload.
     */
    public function withStopContext(?Bus $bus, ?array $location): ?array
    {
        if (! $bus || $location === null) {
            return $location;
        }

        $trip = Trip::where('bus_id', $bus->id)
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->with(['route.stops', 'driver'])
            ->latest('started_at')
            ->first();

        if (! $trip || ! $trip->route) {
            return $location;
        }

        $stops = $trip->route->stops ?? $trip->route->stops()->where('is_active', true)->get();
        if ($stops->isEmpty()) {
            return $location;
        }

        $arrivals = TripStopArrival::where('trip_id', $trip->id)->get()->keyBy('route_stop_id');
        $reachedStop = $this->latestArrivedStop($stops, $arrivals);

        $location['route_id'] = $trip->route_id;
        $location['trip_id'] = $trip->id;

        if ($reachedStop) {
            $location['arrived_stop'] = $this->arrivedStopPayload($reachedStop, $arrivals);
            $location['next_stop'] = $this->nextStopPayload($stops, $reachedStop, $arrivals, $location);
            $location['is_at_final_stop'] = $reachedStop['stop_order'] === (int) $stops->last()->stop_order;
        } else {
            $location['next_stop'] = $this->nextStopPayload($stops, null, $arrivals, $location);
        }

        return $location;
    }

    /**
     * The furthest stop that has been marked as arrived (relative to route order).
     *
     * @param  Collection<int, RouteStop>  $stops
     * @param  Collection<int, TripStopArrival>  $arrivals
     */
    protected function latestArrivedStop($stops, $arrivals): ?RouteStop
    {
        $reached = null;

        foreach ($stops as $stop) {
            if (($arrivals->get($stop->id))?->isArrived()) {
                if (! $reached || $stop->stop_order > $reached->stop_order) {
                    $reached = $stop;
                }
            }
        }

        return $reached;
    }

    /**
     * @param  Collection<int, TripStopArrival>  $arrivals
     */
    protected function arrivedStopPayload(RouteStop $stop, $arrivals): array
    {
        $arrival = $arrivals->get($stop->id);

        return [
            'id' => $stop->id,
            'name' => $stop->name,
            'stop_order' => (int) $stop->stop_order,
            'schedule_time' => $stop->pickup_time ?: $stop->drop_time,
            'arrived_at' => $arrival?->arrived_at?->toIso8601String(),
            'punctuality' => $arrival?->punctuality,
            'lateness_minutes' => $arrival?->lateness_minutes ?? 0,
        ];
    }

    /**
     * @param  Collection<int, RouteStop>  $stops
     * @param  Collection<int, TripStopArrival>  $arrivals
     */
    protected function nextStopPayload($stops, ?RouteStop $reached, $arrivals, ?array $location = null): ?array
    {
        $reachedOrder = $reached?->stop_order;

        $next = $stops->filter(function (RouteStop $stop) use ($reachedOrder) {
            return floatval($stop->stop_order) > floatval($reachedOrder ?? -1);
        })->sortBy('stop_order')->first();

        if (! $next) {
            return null;
        }

        $lat = $location['latitude'] ?? null;
        $lng = $location['longitude'] ?? null;

        $distance = null;
        if ($lat !== null && $lng !== null && is_numeric($next->latitude) && is_numeric($next->longitude)) {
            $distance = $this->haversineKm((float) $lat, (float) $lng, (float) $next->latitude, (float) $next->longitude);
        }

        $speed = (float) ($location['speed_kmh'] ?? 0);
        $eta = ($distance !== null && $speed > 0 && $distance > 0)
            ? round(($distance / $speed) * 60, 1)
            : null;

        return [
            'id' => $next->id,
            'name' => $next->name,
            'stop_order' => (int) $next->stop_order,
            'schedule_time' => $next->pickup_time ?: $next->drop_time,
            'distance_km' => $distance !== null ? round($distance, 3) : null,
            'eta_minutes' => $eta,
        ];
    }

    protected function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $toRad = fn ($deg) => $deg * M_PI / 180;
        $earthRadius = 6371;

        $dLat = $toRad($lat2 - $lat1);
        $dLng = $toRad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
