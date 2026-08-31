<?php

namespace App\Services;

use App\Models\Bus;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NazarTrackService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected int $timeout;

    protected int $cacheTtl;

    /**
     * Live devices fetched in this request, indexed by IMEI.
     *
     * @var array<string, array>|null
     */
    protected ?array $devicesByImei = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('gps.base_url'), '/');
        $this->apiKey = config('gps.api_key');
        $this->timeout = config('gps.timeout', 10);
        $this->cacheTtl = config('gps.cache_ttl', 30);
    }

    /**
     * Get all live GPS devices.
     */
    public function getLiveTracking(): array
    {
        return Cache::remember(
            'gps_live_tracking',
            now()->addSeconds($this->cacheTtl),
            function () {

                $response = Http::timeout($this->timeout)
                    ->withToken($this->apiKey)
                    ->acceptJson()
                    ->withoutVerifying()
                    ->get($this->baseUrl.'/api/ext/live-tracking');

                if (! $response->successful()) {
                    throw new Exception(
                        'GPS API Error: '.
                            $response->status().
                            ' - '.
                            $response->body()
                    );
                }

                return $response->json();
            }
        );
    }

    /**
     * Fetch the live device list once per request and index it by IMEI.
     * Returns an empty array when the provider is unreachable so callers
     * can degrade gracefully (every bus simply shows as offline).
     *
     * @return array<string, array>
     */
    protected function devicesIndexedByImei(): array
    {
        if ($this->devicesByImei !== null) {
            return $this->devicesByImei;
        }

        $this->devicesByImei = [];

        try {
            $response = $this->getLiveTracking();

            foreach ($response['data'] ?? [] as $device) {
                $imei = $device['imei'] ?? null;

                if ($imei !== null) {
                    $this->devicesByImei[$imei] = $device;
                }
            }
        } catch (Exception $e) {
            $this->devicesByImei = [];
        }

        return $this->devicesByImei;
    }

    /**
     * Find a GPS device by its IMEI.
     */
    public function findDeviceByImei(string $imei): ?array
    {
        return $this->devicesIndexedByImei()[$imei] ?? null;
    }

    /**
     * Get live GPS data for a Bus model.
     */
    public function getBusLocation(Bus $bus): ?array
    {
        if (empty($bus->gps_device_id)) {
            return null;
        }

        return $this->findDeviceByImei($bus->gps_device_id);
    }

    /**
     * Get live GPS data for many buses in a single API request.
     *
     * @param  iterable<Bus>  $buses
     * @return array<int, array|null> keyed by bus id
     */
    public function getBusLocations(iterable $buses): array
    {
        $result = [];
        $indexed = $this->devicesIndexedByImei();

        foreach ($buses as $bus) {
            if (! empty($bus->gps_device_id)) {
                $result[$bus->id] = $indexed[$bus->gps_device_id] ?? null;
            }
        }

        return $result;
    }

    /**
     * Get the raw NazarTrack API payload for a bus (original API response).
     */
    public function locationPayload(?Bus $bus): ?array
    {
        if (! $bus || empty($bus->gps_device_id)) {
            return null;
        }

        $location = $this->getBusLocation($bus);

        if (! $location) {
            return null;
        }

        // Ensure IMEI is present (fallback to bus's gps_device_id)
        if (! isset($location['imei'])) {
            $location['imei'] = $bus->gps_device_id;
        }

        return $location;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'moving' => 'Moving',
            'stopped' => 'Stopped',
            'idle' => 'Idle',
            'offline' => 'Offline',
            default => ucfirst($status),
        };
    }

    /**
     * Build a normalized "last known location" payload from the latest stored
     * BusLocation row. Used as a fallback when the GPS provider has no live fix
     * for the bus, so the API still returns coordinates instead of null.
     *
     * @return array|null The last known payload, or null when no fix is stored.
     */
    public function lastKnownPayload(?Bus $bus): ?array
    {
        if (! $bus || ! $bus->gpsDevice) {
            return null;
        }

        $last = $bus->gpsDevice->locations()
            ->latest('recorded_at')
            ->first();

        if (! $last || $last->latitude === null || $last->longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $last->latitude,
            'longitude' => (float) $last->longitude,
            'speed_kmh' => (float) $last->speed,
            'course' => (float) $last->heading,
            'status' => 'offline',
            'status_label' => self::statusLabel('offline'),
            'status_color' => self::statusColor('offline'),
            'is_moving' => false,
            'gps_time' => $last->recorded_at?->toIso8601String(),
            'last_updated_at' => $last->recorded_at?->toIso8601String(),
            'last_updated_ago' => $last->recorded_at?->diffForHumans(),
            'asset_name' => $bus->gpsDevice->device_name,
            'imei' => $bus->gpsDevice->device_imei ?: $bus->gps_device_id,
            'animate' => false,
            'marker' => ['heading' => (float) $last->heading],
        ];
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'moving' => '#22c55e',
            'stopped' => '#f59e0b',
            'idle' => '#eab308',
            default => '#6b7280',
        };
    }
}
