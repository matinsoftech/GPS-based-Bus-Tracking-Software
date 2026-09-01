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
     * Build the map payload for a single route, used by the parent tracking page.
     *
     * Unlike forSchool() (which only includes routes that currently have an active
     * trip), this always includes the given route with its stops so the parent can
     * see the route path even before a trip starts. The bus marker is only included
     * when an active trip provides a bus for the route.
     *
     * @param  Route  $route  The assigned route to render.
     * @param  array|null  $location  A normalized device payload from NazarTrackService.
     */
    public function forRoute(Route $route, ?array $location = null): array
    {
        $route->loadMissing(['stops']);

        $bus = $route->activeTrip?->bus;

        $busArray = $bus
            ? $this->busToArray($bus, $location, $route)
            : null;

        $routeData = $this->routeToArray($route);

        return [
            'buses' => $busArray ? [$busArray] : [],
            'routes' => $routeData === null ? [] : [$routeData],
            'summary' => [
                'total' => $busArray ? 1 : 0,
                'active' => $busArray && $busArray['status'] === 'Active' ? 1 : 0,
                'maintenance' => $busArray && $busArray['status'] === 'Maintenance' ? 1 : 0,
                'inactive' => $busArray && $busArray['status'] === 'Inactive' ? 1 : 0,
                'moving' => $busArray && $busArray['tracking_status'] === 'moving' ? 1 : 0,
                'stopped' => $busArray && in_array($busArray['tracking_status'], ['stopped', 'idle'], true) ? 1 : 0,
                'idle' => $busArray && $busArray['tracking_status'] === 'idle' ? 1 : 0,
                'offline' => $busArray && in_array($busArray['tracking_status'], ['offline', 'inactive'], true) ? 1 : 0,
                'routes_running' => ($route->is_active && $busArray) ? 1 : 0,
            ],
            'school' => $route->school ? $route->school->only(['name', 'latitude', 'longitude']) : null,
            'updated_at' => now()->toIso8601String(),
        ];
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
            'routes' => $routes->map(fn (Route $route) => $this->routeToArray($route))->filter()->values()->all(),
            'summary' => [
                'total' => $buses->count(),
                'active' => $buses->where('status', 'Active')->count(),
                'maintenance' => $buses->where('status', 'Maintenance')->count(),
                'inactive' => $buses->where('status', 'Inactive')->count(),
                'moving' => $busArrays->where('tracking_status', 'moving')->count(),
                'stopped' => $busArrays->whereIn('tracking_status', ['stopped', 'idle'])->count(),
                'idle' => $busArrays->where('tracking_status', 'idle')->count(),
                'offline' => $busArrays->whereIn('tracking_status', ['offline', 'inactive'])->count(),
                'routes_running' => $routes->where('is_active', true)->count(),
            ],
            'school' => $schoolId
                ? School::find($schoolId)?->only(['name', 'latitude', 'longitude'])
                : null,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Serialize a route (with its stops) into the map payload shape.
     *
     * @return array{id: int, name: string, route_code: string, start_location: string|null, end_location: string|null, stops: array}|null
     */
    private function routeToArray(Route $route): ?array
    {
        if ($route->stops->isEmpty()) {
            return null;
        }

        return [
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
        ];
    }

    /**
     * Serialize a bus (with its latest API location) into the map payload shape.
     *
     * @param  array|null  $location  A normalized device payload from NazarTrackService.
     * @param  Route|Collection  $routeOrTrips  The bus's route, or the active trips to resolve it from.
     */
    private function busToArray(Bus $bus, ?array $location, Route|Collection $routeOrTrips): array
    {
        $route = $routeOrTrips instanceof Route
            ? $routeOrTrips
            : $routeOrTrips->firstWhere('bus_id', $bus->id)?->route;

        $nearestStop = $location && $route ? $this->nearestStop($route, $location) : null;
        $status = $this->trackingStatus($location);

        $statusLabel = match ($status) {
            'moving' => 'Moving',
            'stopped' => 'Stopped',
            'idle' => 'Idle',
            default => $location['status_label'] ?? ucfirst($status),
        };

        $statusColor = match ($status) {
            'moving' => '#22c55e',
            'stopped' => '#f59e0b',
            'idle' => '#eab308',
            default => $location['status_color'] ?? '#6b7280',
        };

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
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'is_online' => (bool) ($location['is_online'] ?? false),
        ];
    }

    /**
     * Resolve the per-bus tracking status, mirroring VehicleTrackingController::deriveStatus.
     *
     * - Moving:  speed > 0 or moving_since present (takes priority, even if offline flag is set).
     * - inactive/offline: not live on the GPS provider / has no position.
     * - Idle:    online, stopped, and idle_since present.
     * - Stopped: online, stationary, not idle.
     */
    private function trackingStatus(?array $location): string
    {
        if (! $location) {
            return 'inactive';
        }

        $speed = (float) ($location['speed_kmh'] ?? $location['speed'] ?? 0);
        $movingSince = $location['moving_since'] ?? null;
        $idleSince = $location['idle_since'] ?? null;
        $statusSinceLabel = strtolower((string) ($location['status_since_label'] ?? ''));

        $isMoving = $speed > 0
            || $movingSince !== null
            || str_contains($statusSinceLabel, 'moving');

        if ($isMoving) {
            return 'moving';
        }

        if (! (bool) ($location['is_online'] ?? false)) {
            return $location['status'] ?? 'inactive';
        }

        if ($idleSince !== null || str_contains($statusSinceLabel, 'idle')) {
            return 'idle';
        }

        return 'stopped';
    }

    /**
     * Find the nearest configured stop to the current bus position.
     *
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
     */
    private function etaMinutes(string $status, ?array $location, ?array $nearestStop): ?float
    {
        $speed = (float) ($location['speed_kmh'] ?? $location['speed'] ?? 0);

        if ($status !== 'moving' || ! $nearestStop || ! $location || $speed <= 0) {
            return null;
        }

        return round(($nearestStop['distance_km'] / $speed) * 60, 1);
    }

    /**
     * Whether the provider marks the device online with a valid position.
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
