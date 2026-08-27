<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\Trip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the data payload used by the fleet monitoring map.
 *
 * The payload is intentionally plain PHP arrays (no model instances) so it can be
 * embedded directly into a Blade view with @json and re-serialized by a JSON
 * endpoint without any mapping differences.
 *
 * Live bus positions are sourced from the NazarTrack GPS provider API through
 * NazarTrackService (the BusLocation table is only used by the legacy
 * latestLocationsByDevice() helper).
 */
class FleetMapService
{
    /** A location is considered live when recorded within this many minutes. */
    public const STALE_MINUTES = 10;

    /** A live bus is considered stopped when speed is at or below this (km/h). */
    public const STOPPED_SPEED_KPH = 3;

    /** Distance (km) within which a stopped bus is considered "Arrived" at a stop. */
    public const ARRIVED_RADIUS_KM = 0.25;

    public function __construct(private readonly NazarTrackService $gps) {}

    /**
     * Latest BusLocation per GPS device, optionally restricted to a set of bus ids.
     *
     * This is the shared "latest location per device" query used by the bus
     * location page, the driver dashboard and the fleet map.
     *
     * @param  Collection<int, int>|null  $busIds  Restrict to these bus ids (null = all devices).
     * @param  array  $with  Relations to eager load on each BusLocation.
     */
    public function latestLocationsByDevice(?Collection $busIds = null, array $with = ['gpsDevice']): Collection
    {
        if ($busIds !== null && $busIds->isEmpty()) {
            return collect();
        }

        $latestPerDevice = BusLocation::select('gps_device_id')
            ->selectRaw('MAX(recorded_at) as last_recorded_at')
            ->groupBy('gps_device_id');

        return BusLocation::query()
            ->joinSub($latestPerDevice, 'latest', function ($join) {
                $join->on('bus_locations.gps_device_id', '=', 'latest.gps_device_id')
                    ->on('bus_locations.recorded_at', '=', 'latest.last_recorded_at');
            })
            ->with($with)
            ->when($busIds !== null, fn ($query) => $query
                ->whereHas('gpsDevice.bus', fn ($bus) => $bus->whereIn('id', $busIds)))
            ->orderByDesc('bus_locations.recorded_at')
            ->get();
    }

    /**
     * Build the full fleet map payload for a school.
     *
     * @param  int|null  $schoolId  Null = all schools (used by super admin contexts).
     * @param  Collection<int, int>|null  $busIds  Restrict to these bus ids (e.g. a driver's buses).
     */
    public function forSchool(?int $schoolId, ?Collection $busIds = null): array
    {
        $busQuery = Bus::query()->with(['drivers', 'gpsDevice', 'school']);

        if ($schoolId) {
            $busQuery->where('school_id', $schoolId);
        }

        if ($busIds !== null) {
            $busQuery->whereIn('id', $busIds);
        }

        $buses = $busQuery->get();

        $locationsByBus = $this->gps->getBusLocations($buses);

        // Build route info from active trips on these buses
        $activeTrips = Trip::whereIn('bus_id', $buses->pluck('id'))
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->with(['route', 'driver'])
            ->get();

        $busArrays = $buses->map(
            fn (Bus $bus) => $this->busToArray($bus, $locationsByBus[$bus->id] ?? null, $activeTrips)
        )->values();

        $routeQuery = Route::query()->with('stops');

        if ($schoolId) {
            $routeQuery->where('school_id', $schoolId);
        }

        // Get routes that have active trips
        $routeIds = $activeTrips->pluck('route_id')->unique()->values();
        $routeQuery->whereIn('id', $routeIds);

        $routes = $routeQuery->orderBy('name')->get();

        return [
            'buses' => $busArrays->all(),
            'routes' => $routes->map(fn (Route $route) => [
                'id' => $route->id,
                'name' => $route->name,
                'route_code' => $route->route_code,
                'start_location' => $route->start_location,
                'end_location' => $route->end_location,
                'stops' => $route->stops->map(fn (RouteStop $stop) => [
                    'id' => $stop->id,
                    'name' => $stop->name,
                    'latitude' => $stop->latitude,
                    'longitude' => $stop->longitude,
                    'stop_order' => $stop->stop_order,
                ])->values()->all(),
            ])->values()->all(),
            'summary' => [
                'total' => $buses->count(),
                'active' => $buses->where('status', 'Active')->count(),
                'maintenance' => $buses->where('status', 'Maintenance')->count(),
                'inactive' => $buses->where('status', 'Inactive')->count(),
                'moving' => $busArrays->where('tracking_status', 'Moving')->count(),
                'stopped' => $busArrays->whereIn('tracking_status', ['Stopped', 'Arrived'])->count(),
                'routes_running' => $routes->where('is_active', true)->count(),
            ],
            'school' => $schoolId
                ? School::find($schoolId)?->only(['name', 'latitude', 'longitude'])
                : null,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Serialize a bus (with its latest API location) into the map payload shape.
     *
     * @param  array|null  $location  A normalized device payload from NazarTrackService.
     * @param  Collection  $activeTrips  Active trips to resolve route info.
     */
    private function busToArray(Bus $bus, ?array $location, Collection $activeTrips): array
    {
        $activeTrip = $activeTrips->firstWhere('bus_id', $bus->id);
        $route = $activeTrip?->route;

        $nearestStop = $location && $route ? $this->nearestStop($route, $location) : null;
        $status = $this->trackingStatus($bus, $location, $nearestStop);

        return [
            'id' => $bus->id,
            'bus_number' => $bus->bus_number,
            'registration_number' => $bus->registration_number,
            'status' => $bus->status,
            'driver_name' => $bus->drivers->first()?->full_name,
            'route_id' => $route?->id,
            'route_name' => $route?->name,
            'school_name' => $bus->school?->name,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'speed' => (float) ($location['speed_kmh'] ?? $location['speed'] ?? 0),
            'heading' => $location['course'] ?? ($location['marker']['heading'] ?? null),
            'recorded_at' => $this->gpsTimestampIso($location),
            'tracking_status' => $status,
            'next_stop' => $nearestStop['stop']?->name ?? null,
            'eta_minutes' => $this->etaMinutes($status, $location, $nearestStop),
            'imei' => $location['imei'] ?? $bus->gps_device_id ?? null,
            'last_updated_ago' => $location['last_updated_ago'] ?? null,
            'status_label' => $location['status_label'] ?? null,
            'status_color' => $location['status_color'] ?? null,
            'is_online' => (bool) ($location['is_online'] ?? false),
        ];
    }

    /**
     * Resolve the per-bus tracking status.
     *
     * - Offline: bus is not online on the GPS provider / no fresh location.
     * - Arrived: stopped within ARRIVED_RADIUS_KM of a configured stop.
     * - Moving:  online with speed above the stopped threshold.
     * - Stopped: online, but effectively stationary.
     *
     * @param  array|null  $location
     */
    private function trackingStatus(Bus $bus, ?array $location, ?array $nearestStop): string
    {
        if (! $this->isLive($location)) {
            return 'Offline';
        }

        $stopped = (float) ($location['speed_kmh'] ?? $location['speed'] ?? 0) <= self::STOPPED_SPEED_KPH;

        if ($stopped && $nearestStop && $nearestStop['distance_km'] <= self::ARRIVED_RADIUS_KM) {
            return 'Arrived';
        }

        return $stopped ? 'Stopped' : 'Moving';
    }

    /**
     * Find the nearest configured stop to the current bus position.
     *
     * @param  array  $location
     * @return array{stop: RouteStop|null, distance_km: float|null}|null
     */
    private function nearestStop(Route $route, array $location): ?array
    {
        $lat = $location['latitude'] ?? null;
        $lng = $location['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            return null;
        }

        $stops = $route->stops;

        if ($stops->isEmpty()) {
            return null;
        }

        $nearest = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($stops as $stop) {
            $distance = $this->haversineKm(
                (float) $lat,
                (float) $lng,
                (float) $stop->latitude,
                (float) $stop->longitude,
            );

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $stop;
            }
        }

        if (! $nearest) {
            return null;
        }

        return [
            'stop' => $nearest,
            'distance_km' => $nearestDistance,
        ];
    }

    /**
     * Estimated minutes to reach the next stop (based on current speed).
     *
     * @param  array|null  $location
     */
    private function etaMinutes(string $status, ?array $location, ?array $nearestStop): ?float
    {
        $speed = (float) ($location['speed_kmh'] ?? $location['speed'] ?? 0);

        if ($status !== 'Moving' || ! $nearestStop || ! $location || $speed <= 0) {
            return null;
        }

        return round(($nearestStop['distance_km'] / $speed) * 60, 1);
    }

    /**
     * Whether the provider marks the device online with a valid position.
     *
     * @param  array|null  $location
     */
    private function isLive(?array $location): bool
    {
        if (! $location) {
            return false;
        }

        if (empty($location['latitude']) || empty($location['longitude'])) {
            return false;
        }

        // Trust the GPS provider's own online flag when it is present.
        if (array_key_exists('is_online', $location) && ! $location['is_online']) {
            return false;
        }

        return true;
    }

    /**
     * Normalize the provider timestamp into an ISO-8601 string.
     *
     * @param  array|null  $location
     */
    private function gpsTimestampIso(?array $location): ?string
    {
        if (! $location) {
            return null;
        }

        $gpsTime = $location['gps_time'] ?? null;

        if ($gpsTime) {
            try {
                return Carbon::parse($gpsTime)->toIso8601String();
            } catch (\Exception $e) {
                // fall through to the provider's last_updated_at field
            }
        }

        $lastUpdated = $location['last_updated_at'] ?? null;

        if ($lastUpdated) {
            try {
                return Carbon::parse($lastUpdated, config('app.timezone'))->toIso8601String();
            } catch (\Exception $e) {
                // give up and return null below
            }
        }

        return null;
    }

    /**
     * Haversine distance between two coordinates (km).
     */
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}