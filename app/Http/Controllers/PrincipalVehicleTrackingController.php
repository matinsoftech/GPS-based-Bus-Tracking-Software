<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Services\NazarTrackService;
use Illuminate\Support\Facades\Auth;

class PrincipalVehicleTrackingController extends Controller
{
    private const STATUS_META = [
        'moving'   => ['label' => 'Moving',   'color' => '#22c55e'],
        'idle'     => ['label' => 'Idle',     'color' => '#f59e0b'],
        'stopped'  => ['label' => 'Stopped',  'color' => '#ef4444'],
        'offline'  => ['label' => 'Offline',  'color' => '#ef4444'],
        'inactive' => ['label' => 'Inactive', 'color' => '#64748b'],
    ];

    private const STATUS_SORT_ORDER = [
        'moving'   => 0,
        'stopped'  => 1,
        'idle'     => 2,
        'inactive' => 3,
        'offline'  => 4,
    ];

    public function __construct(private readonly NazarTrackService $gps) {}

    public function index()
    {
        $vehicles = $this->buildVehicleList();

        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);
        $school = $schoolId ? School::find($schoolId) : null;

        return view('principal-vehicle-tracking', compact('vehicles', 'school'));
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
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

        $response = $this->gps->getLiveTracking();
        $apiVehicles = $response['data'] ?? [];

        $schoolBuses = Bus::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['drivers', 'school'])
            ->get()
            ->keyBy('gps_device_id');

        $vehicles = [];

        foreach ($apiVehicles as $vehicle) {
            $matchedBus = $this->matchBus($vehicle, $schoolBuses);

            if (! $matchedBus) {
                continue;
            }

            $vehicles[] = $this->mapVehicle($vehicle, $matchedBus);
        }

        usort($vehicles, fn ($a, $b) => $this->statusSortValue($a['status']) <=> $this->statusSortValue($b['status']));

        return $vehicles;
    }

    private function matchBus(array $vehicle, $schoolBuses): ?Bus
    {
        $imei = $vehicle['imei'] ?? null;

        return $imei ? $schoolBuses->get($imei) : null;
    }

    private function mapVehicle(array $vehicle, Bus $matchedBus): array
    {
        $status = $this->resolveVehicleStatus($vehicle);
        $meta = self::STATUS_META[$status] ?? self::STATUS_META['inactive'];

        return [
            'asset_id' => $vehicle['asset_id'] ?? null,
            'asset_name' => $vehicle['asset_name'] ?? 'Unknown',
            'plate_number' => $vehicle['plate_number'] ?? null,
            'imei' => $vehicle['imei'] ?? null,
            'latitude' => $vehicle['latitude'] ?? null,
            'longitude' => $vehicle['longitude'] ?? null,
            'speed_kmh' => (float) ($vehicle['speed_kmh'] ?? 0),
            'status' => $status,
            'status_label' => $meta['label'],
            'status_color' => $meta['color'],
            'status_since_ago' => $vehicle['status_since_ago'] ?? null,
            'is_online' => (bool) ($vehicle['is_online'] ?? false),
            'is_moving' => $status === 'moving',
            'ignition' => $vehicle['ignition'] ?? null,
            'gps_time' => $vehicle['gps_time'] ?? null,
            'last_updated_ago' => $vehicle['last_updated_ago'] ?? null,
            'driver_name' => $vehicle['driver']['name'] ?? null,
            'driver_phone' => $vehicle['driver']['phone'] ?? null,
            'bus_id' => $matchedBus->id,
            'bus_number' => $matchedBus->bus_number,
            'bus_registration' => $matchedBus->registration_number,
            'school_name' => $matchedBus->school?->name,
            'matched_driver_name' => $matchedBus->drivers->first()?->full_name,
        ];
    }

    private function resolveVehicleStatus(array $vehicle): string
    {
        if (! (bool) ($vehicle['is_online'] ?? false)) {
            return 'offline';
        }

        $speed = (float) ($vehicle['speed_kmh'] ?? 0);

        if (! empty($vehicle['moving_since']) || $speed > 0) {
            return 'moving';
        }

        if (! empty($vehicle['idle_since'])) {
            return 'idle';
        }

        if (! empty($vehicle['stopped_since'])) {
            return 'stopped';
        }

        return 'inactive';
    }

    private function statusSortValue(string $status): int
    {
        return self::STATUS_SORT_ORDER[strtolower($status)] ?? 5;
    }

    private function resolveSchoolId($user): ?int
    {
        $schoolId = $user->school_id;

        if (! $schoolId) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        if (! $schoolId) {
            $schoolId = School::where('principal_name', $user->name)
                ->orWhere('email', $user->email)
                ->value('id');
        }

        return $schoolId;
    }
}
