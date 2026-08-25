<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\GpsDevice;
use App\Models\SchoolAdmin;
use App\Models\User;
use App\Notifications\BusStartedNotification;
use Illuminate\Support\Facades\Cache;
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

        // Determine whether the bus was online before this update, so we only
        // notify when it transitions from offline -> online.
        $wasOnline = $this->wasRecentlyOnline($gpsDevice);

        if ($hasFix) {
            $gpsDevice->locations()->create([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => (float) ($data['speed_kmh'] ?? 0),
                'heading' => $data['course'] ?? null,
                'altitude' => $data['altitude'] ?? null,
                'recorded_at' => now(),
            ]);
        }

        if ($hasFix && ! $wasOnline && $this->canNotifyBusStarted($bus)) {
            $this->notifyBusStarted($bus);
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

    private function wasRecentlyOnline(GpsDevice $gpsDevice): bool
    {
        $lastLocation = $gpsDevice->locations()
            ->latest('recorded_at')
            ->first();

        if (! $lastLocation) {
            return false;
        }

        $threshold = (int) config('gps.offline_threshold_minutes', 10);

        return $lastLocation->recorded_at
            ->greaterThanOrEqualTo(now()->subMinutes($threshold));
    }

    private function canNotifyBusStarted(Bus $bus): bool
    {
        $cooldown = (int) config('gps.bus_started_cooldown_minutes', 180);

        return Cache::add(
            "bus_started_notified:{$bus->id}",
            now()->toDateTimeString(),
            now()->addMinutes($cooldown),
        );
    }

    private function notifyBusStarted(Bus $bus): void
    {
        $notification = new BusStartedNotification($bus);

        // Notify parents of students on this bus
        $students = $bus->students()
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            $parent = $student->parent?->user;

            if (! $parent) {
                continue;
            }

            $parent->notify($notification);
        }

        // Notify the driver assigned to this bus
        $driverUser = $bus->drivers->first()?->user;

        if ($driverUser) {
            $driverUser->notify($notification);
        }

        // Notify all school admins for this bus's school
        $schoolAdmins = SchoolAdmin::where('school_id', $bus->school_id)
            ->with('user')
            ->get();

        foreach ($schoolAdmins as $admin) {
            if (! $admin->user) {
                continue;
            }

            $admin->user->notify($notification);
        }

        // Notify all super admins
        $superAdmins = User::role('Super Admin')->get();

        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify($notification);
        }
    }
}
