<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\GpsDevice;
use Illuminate\Support\Facades\Log;

class BusTrackingService
{
    public function processLocation(array $data): void
    {
        $imei = $data['imei'] ?? null;

        if (! $imei) {
            Log::debug('GPS device payload missing IMEI', ['data' => $data]);

            return;
        }

        $bus = Bus::where('gps_device_id', $imei)->first();

        if (! $bus) {
            Log::debug('GPS device not linked to a bus', [
                'imei' => $imei,
                'asset_name' => $data['asset_name'] ?? null,
            ]);

            return;
        }

        $gpsDevice = $this->resolveGpsDevice($bus, $data);

        if (! $gpsDevice) {
            Log::warning('Could not resolve GPS device for bus', [
                'bus_id' => $bus->id,
                'imei' => $imei,
            ]);

            return;
        }

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $hasFix = is_numeric($latitude) && is_numeric($longitude);

        if ($hasFix) {
            $gpsDevice->locations()->create([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => (float) ($data['speed_kmh'] ?? 0),
                'heading' => $data['course'] ?? null,
                'altitude' => $data['altitude'] ?? null,
                'recorded_at' => now(),
            ]);

            app(StopArrivalService::class)->detectForBus(
                $bus,
                (float) $latitude,
                (float) $longitude,
                (float) ($data['speed_kmh'] ?? 0),
            );
        }
    }

    private function resolveGpsDevice(Bus $bus, array $data): ?GpsDevice
    {
        $gpsDevice = $bus->gpsDevice;

        if ($gpsDevice) {
            return $gpsDevice;
        }

        return GpsDevice::create([
            'school_id' => $bus->school_id,
            'bus_id' => $bus->id,
            'device_name' => $data['asset_name'] ?? $bus->bus_number,
            'device_imei' => $data['imei'],
            'status' => 'active',
        ]);
    }

    public function getLastKnownLocation(int $busId): ?array
    {
        $bus = Bus::with('gpsDevice')->find($busId);

        if (! $bus || ! $bus->gpsDevice) {
            return null;
        }

        $lastLocation = $bus->gpsDevice->locations()->latest('recorded_at')->first();

        if (! $lastLocation) {
            return null;
        }

        return [
            'latitude' => $lastLocation->latitude,
            'longitude' => $lastLocation->longitude,
        ];
    }
}
