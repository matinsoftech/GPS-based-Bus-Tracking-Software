<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverBusTripFlagTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;

    private Driver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-FLAG-1',
            'email' => 'flag@brightfuture.com',
            'phone' => '9800000200',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create([
            'name' => 'Flag Driver',
            'email' => 'flag-driver@example.com',
        ]);
        $this->driverUser->assignRole('Driver');

        $this->driver = Driver::create([
            'school_id' => $school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-FLAG-1',
            'first_name' => 'Flag',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000201',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-FLAG-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);
    }

    private function createBus(string $number): Bus
    {
        return Bus::create([
            'school_id' => $this->driver->school_id,
            'bus_number' => $number,
            'registration_number' => 'REG-'.strtoupper($number),
            'make' => 'Toyota',
            'model' => 'Coaster',
            'capacity' => 30,
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);
    }

    private function createRoute(string $name): Route
    {
        return Route::create([
            'name' => $name,
            'route_code' => 'RT-'.strtoupper(str_replace(' ', '-', $name)),
            'school_id' => $this->driver->school_id,
            'is_active' => true,
            'start_location' => 'Start',
            'end_location' => 'End',
        ]);
    }

    public function test_buses_list_includes_is_in_trip_flag(): void
    {
        $activeBus = $this->createBus('B-101');
        $inTripBus = $this->createBus('B-102');

        $this->driver->buses()->attach([$activeBus->id, $inTripBus->id]);

        Trip::create([
            'bus_id' => $inTripBus->id,
            'driver_id' => $this->driver->id,
            'route_id' => null,
            'school_id' => $this->driver->school_id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/buses')
            ->assertOk();

        $response->assertJsonCount(2, 'data.buses');

        $buses = collect($response->json('data.buses'));

        $this->assertTrue(
            $buses->firstWhere('bus_number', 'B-101')['is_in_trip'] === false
        );
        $this->assertTrue(
            $buses->firstWhere('bus_number', 'B-102')['is_in_trip'] === true
        );
    }

    public function test_routes_list_includes_is_in_trip_flag(): void
    {
        $freeRoute = $this->createRoute('Route A');
        $inTripRoute = $this->createRoute('Route B');

        $this->driver->routes()->attach([$freeRoute->id, $inTripRoute->id]);

        $bus = $this->createBus('B-103');
        $this->driver->buses()->attach($bus->id);

        Trip::create([
            'bus_id' => $bus->id,
            'driver_id' => $this->driver->id,
            'route_id' => $inTripRoute->id,
            'school_id' => $this->driver->school_id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/routes')
            ->assertOk();

        $response->assertJsonCount(2, 'data.routes');

        $routes = collect($response->json('data.routes'));

        $this->assertTrue(
            $routes->firstWhere('name', 'Route A')['is_in_trip'] === false
        );
        $this->assertTrue(
            $routes->firstWhere('name', 'Route B')['is_in_trip'] === true
        );
    }

    public function test_bus_show_includes_is_in_trip_flag(): void
    {
        $bus = $this->createBus('B-104');
        $this->driver->buses()->attach($bus->id);

        Sanctum::actingAs($this->driverUser);

        $this->getJson("/api/v1/driver/buses/{$bus->id}")
            ->assertOk()
            ->assertJsonPath('bus.is_in_trip', false);
    }
}
