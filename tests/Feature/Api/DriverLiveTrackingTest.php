<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverLiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Driver $driver;

    private User $driverUser;

    protected function setUp(): void
    {
        parent::setUp();

        config(['gps.base_url' => 'https://gps.example.com']);
        config(['gps.cache_ttl' => 30]);

        Cache::flush();

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-LT-API',
            'email' => 'ltapi@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create();
        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-LT-API-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-LT-API-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);
    }

    private function fakeLiveTracking(array $devices): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'https://gps.example.com/api/ext/live-tracking' => Http::response([
                'data' => $devices,
            ], 200),
        ]);
    }

    private function makeBus(string $busNumber, ?string $imei = null): Bus
    {
        $bus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => $busNumber,
            'registration_number' => 'BA '.$busNumber,
            'capacity' => 40,
            'gps_device_id' => $imei,
            'status' => 'Active',
        ]);
        $bus->drivers()->attach($this->driver->id);

        return $bus;
    }

    public function test_driver_gets_live_tracking_matched_by_imei(): void
    {
        $imei = '123456789012345';

        $this->makeBus('LT-API-BUS-1', $imei);

        $this->fakeLiveTracking([
            [
                'imei' => $imei,
                'asset_name' => 'Bus 1',
                'latitude' => 27.7172,
                'longitude' => 85.324,
                'speed_kmh' => 45.0,
                'course' => 90,
                'status' => 'moving',
                'status_label' => 'Moving',
                'status_color' => '#22c55e',
                'is_online' => true,
                'gps_time' => now()->toDateTimeString(),
                'last_updated_at' => now()->toDateTimeString(),
            ],
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking')
            ->assertOk()
            ->assertJsonPath('message', 'Driver live tracking data.')
            ->assertJsonPath('data.summary.total', 1)
            ->assertJsonCount(1, 'data.buses')
            ->assertJsonPath('data.buses.0.bus_number', 'LT-API-BUS-1')
            ->assertJsonPath('data.buses.0.imei', $imei)
            ->assertJsonPath('data.buses.0.latitude', 27.7172)
            ->assertJsonPath('data.buses.0.longitude', 85.324)
            ->assertJsonPath('data.buses.0.speed', 45)
            ->assertJsonPath('data.buses.0.heading', 90)
            ->assertJsonPath('data.buses.0.tracking_status', 'moving')
            ->assertJsonPath('data.buses.0.is_online', true)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'buses' => [
                        '*' => [
                            'id',
                            'bus_number',
                            'latitude',
                            'longitude',
                            'speed',
                            'tracking_status',
                            'imei',
                            'is_online',
                        ],
                    ],
                    'routes',
                    'summary' => ['total', 'active', 'maintenance', 'inactive', 'moving', 'stopped', 'routes_running'],
                    'updated_at',
                ],
            ]);
    }

    public function test_bus_without_matching_imei_shows_offline(): void
    {
        $this->makeBus('LT-API-BUS-2', '999999999999999');

        $this->fakeLiveTracking([
            [
                'imei' => '111111111111111',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'speed_kmh' => 0,
                'status' => 'stopped',
                'is_online' => true,
            ],
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking')
            ->assertOk()
            ->assertJsonCount(1, 'data.buses')
            ->assertJsonPath('data.buses.0.latitude', null)
            ->assertJsonPath('data.buses.0.longitude', null)
            ->assertJsonPath('data.buses.0.tracking_status', 'inactive')
            ->assertJsonPath('data.buses.0.is_online', false);
    }

    public function test_bus_without_gps_device_id_shows_offline(): void
    {
        $this->makeBus('LT-API-BUS-3');

        $this->fakeLiveTracking([
            [
                'imei' => '111111111111111',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'is_online' => true,
            ],
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking')
            ->assertOk()
            ->assertJsonCount(1, 'data.buses')
            ->assertJsonPath('data.buses.0.imei', null)
            ->assertJsonPath('data.buses.0.tracking_status', 'inactive');
    }

    public function test_live_tracking_requires_driver_profile(): void
    {
        $parentUser = User::factory()->create();

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($parentUser);

        $this->getJson('/api/v1/driver/live-tracking')
            ->assertNotFound()
            ->assertJsonPath('message', 'Driver profile not found.');
    }

    public function test_live_tracking_can_scope_to_a_single_bus(): void
    {
        $imei = '123456789012345';

        $this->makeBus('LT-API-BUS-1', $imei);
        $bus2 = $this->makeBus('LT-API-BUS-2', '222222222222222');

        $this->fakeLiveTracking([
            [
                'imei' => $imei,
                'latitude' => 27.7172,
                'longitude' => 85.324,
                'speed_kmh' => 45.0,
                'status' => 'moving',
                'is_online' => true,
            ],
            [
                'imei' => '222222222222222',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'speed_kmh' => 0,
                'status' => 'stopped',
                'is_online' => true,
            ],
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking?bus_id='.$bus2->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.buses')
            ->assertJsonPath('data.buses.0.bus_number', 'LT-API-BUS-2');
    }

    public function test_live_tracking_rejects_another_drivers_bus(): void
    {
        $imei = '123456789012345';

        $this->makeBus('LT-API-BUS-1', $imei);

        $otherDriverUser = User::factory()->create();
        $otherDriver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $otherDriverUser->id,
            'employee_id' => 'DR-LT-API-2',
            'first_name' => 'Other',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1985-01-01',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-LT-API-2',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $otherDriverUser->id,
        ]);

        $otherBus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'LT-API-BUS-9',
            'registration_number' => 'BA LT-API-BUS-9',
            'capacity' => 40,
            'gps_device_id' => '888888888888888',
            'status' => 'Active',
        ]);
        $otherBus->drivers()->attach($otherDriver->id);

        $this->fakeLiveTracking([
            [
                'imei' => '888888888888888',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'is_online' => true,
            ],
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking?bus_id='.$otherBus->id)
            ->assertNotFound()
            ->assertJsonPath('message', 'Bus not found for this driver.');
    }

    public function test_live_tracking_degrades_gracefully_when_provider_unreachable(): void
    {
        $this->makeBus('LT-API-BUS-1', '123456789012345');

        Http::preventStrayRequests();

        Http::fake([
            'https://gps.example.com/api/ext/live-tracking' => function () {
                throw new ConnectionException('Provider unreachable');
            },
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/live-tracking')
            ->assertOk()
            ->assertJsonCount(1, 'data.buses')
            ->assertJsonPath('data.buses.0.tracking_status', 'inactive');
    }
}
