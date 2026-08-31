<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Services\NazarTrackService;

class VehicleTrackingController extends Controller
{
    public function __construct(private readonly NazarTrackService $gps) {}

    public function index()
    {
        $vehicles = $this->buildVehicleList();

        return view('vehicle-tracking', compact('vehicles'));
    }

    public function data()
    {
        return response()->json([
            'vehicles' => $this->buildVehicleList(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function buildVehicleList(): array
    {
        $response = $this->gps->getLiveTracking();
        $apiVehicles = $response['data'] ?? [];

        $buses = Bus::all()->keyBy('gps_device_id');

        $vehicles = [];

        foreach ($apiVehicles as $vehicle) {
            $imei = $vehicle['imei'] ?? null;
            $matchedBus = $imei ? ($buses->get($imei) ?? null) : null;

            $status = $this->deriveStatus($vehicle);
            $speed = (float) ($vehicle['speed_kmh'] ?? 0);

            $vehicles[] = [
                'asset_id' => $vehicle['asset_id'] ?? null,
                'asset_name' => $vehicle['asset_name'] ?? 'Unknown',
                'plate_number' => $vehicle['plate_number'] ?? null,
                'imei' => $imei,
                'latitude' => $vehicle['latitude'] ?? null,
                'longitude' => $vehicle['longitude'] ?? null,
                'speed_kmh' => $speed,
                'status' => $status,
                'status_label' => $status === 'moving'
                    ? 'Moving'
                    : ($status === 'stopped'
                        ? 'Stopped'
                        : ($vehicle['status_label'] ?? ucfirst($status))),
                'status_color' => $status === 'moving'
                    ? '#22c55e'
                    : ($status === 'stopped'
                        ? '#f59e0b'
                        : ($vehicle['status_color'] ?? '#6b7280')),
                'is_online' => (bool) ($vehicle['is_online'] ?? false),
                'is_moving' => (bool) ($vehicle['is_moving'] ?? false),
                'gps_time' => $vehicle['gps_time'] ?? null,
                'last_updated_ago' => $vehicle['last_updated_ago'] ?? null,
                'driver_name' => $vehicle['driver']['name'] ?? null,
                'driver_phone' => $vehicle['driver']['phone'] ?? null,
                'bus_id' => $matchedBus?->id,
                'bus_number' => $matchedBus?->bus_number,
                'bus_registration' => $matchedBus?->registration_number,
                'school_name' => $matchedBus?->school?->name,
                'matched_driver_name' => $matchedBus?->drivers->first()?->full_name,
            ];
        }

        usort($vehicles, function ($a, $b) {
            $order = ['moving' => 0, 'stopped' => 1, 'idle' => 2, 'inactive' => 3, 'offline' => 4];
            $aOrder = $order[strtolower($a['status'])] ?? 5;
            $bOrder = $order[strtolower($b['status'])] ?? 5;
            return $aOrder <=> $bOrder;
        });

        return $vehicles;
    }

    private function deriveStatus(array $vehicle): string
    {
        $speed = (float) ($vehicle['speed_kmh'] ?? 0);
        $movingSince = $vehicle['moving_since'] ?? null;
        $statusSinceLabel = strtolower((string) ($vehicle['status_since_label'] ?? ''));

        $isMoving = $speed > 0
            || $movingSince !== null
            || str_contains($statusSinceLabel, 'moving');

        if ($isMoving) {
            return 'moving';
        }

        if (! (bool) ($vehicle['is_online'] ?? false)) {
            return $vehicle['status'] ?? 'inactive';
        }

        return 'stopped';
    }
}
