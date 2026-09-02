<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\Student;
use App\Models\Trip;
use App\Models\TripStopArrival;
use App\Models\User;
use App\Services\StopArrivalService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StopArrivalTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Bus $bus;

    private Route $route;

    private Trip $trip;

    private ParentProfile $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = User::factory()->create(['email' => 'arrival-driver@example.com']);
        $driverUser->assignRole('Driver');

        $parentUser = User::factory()->create(['email' => 'arrival-parent@example.com']);
        $parentUser->assignRole('Parent');

        $this->school = School::create([
            'name' => 'Arrival School',
            'code' => 'SCH-ARR-1',
            'email' => 'arrival@school.com',
            'phone' => '9800000300',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parent = ParentProfile::create([
            'school_id' => $this->school->id,
            'user_id' => $parentUser->id,
            'name' => 'Parent One',
            'father_name' => 'Papa',
            'phone' => '9800000301',
            'address' => 'Kathmandu',
        ]);

        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR-ARR-1',
            'first_name' => 'Arr',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000303',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-ARR-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $this->bus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'B-ARR-1',
            'registration_number' => 'REG-ARR-1',
            'make' => 'Toyota',
            'model' => 'Coaster',
            'capacity' => 30,
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $this->route = Route::create([
            'name' => 'Baneshwor Shuttle',
            'route_code' => 'RT-ARR-1',
            'school_id' => $this->school->id,
            'is_active' => true,
            'start_location' => 'Baneshwor',
            'end_location' => 'School',
        ]);

        $this->createStop(1, 'Baneshwor Chowk', 27.6993300, 85.3392200, '07:00:00');
        $this->route->stops()->create([
            'name' => 'New Baneshwor',
            'latitude' => 27.6958000,
            'longitude' => 85.3348000,
            'stop_order' => 2,
            'pickup_time' => '07:05:00',
            'is_active' => true,
        ]);

        $this->trip = Trip::create([
            'bus_id' => $this->bus->id,
            'driver_id' => $this->driver->id,
            'route_id' => $this->route->id,
            'school_id' => $this->school->id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'route_id' => $this->route->id,
            'admission_no' => 'ADM-ARR-1',
            'first_name' => 'Kid',
            'last_name' => 'One',
            'gender' => 'Male',
            'grade' => '5',
            'pickup_location' => 'Baneshwor Chowk',
            'drop_location' => 'School',
            'date_of_birth' => '2015-01-01',
            'is_active' => true,
            'created_by' => $driverUser->id,
        ]);
    }

    private Driver $driver;

    private function createStop(int $order, string $name, float $lat, float $lng, ?string $pickup, ?string $drop = null): RouteStop
    {
        return $this->route->stops()->create([
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $lng,
            'stop_order' => $order,
            'pickup_time' => $pickup,
            'drop_time' => $drop,
            'is_active' => true,
        ]);
    }

    public function test_detects_arrival_at_stop_and_creates_trip_stop_arrival(): void
    {
        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6993300,
            85.3392200,
            0.0,
        );

        $this->assertDatabaseHas('trip_stop_arrivals', [
            'trip_id' => $this->trip->id,
            'status' => 'arrived',
        ]);

        $arrival = TripStopArrival::where('trip_id', $this->trip->id)->first();
        $this->assertSame('arrived', $arrival->status);
        $this->assertSame('07:00:00', date('H:i:s', strtotime($arrival->schedule_at)));
        $this->assertNull($arrival->departed_at);
    }

    public function test_notifies_parent_when_stop_reached(): void
    {
        $this->parent->user->notifications()->delete();

        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6993300,
            85.3392200,
            0.0,
        );

        $notification = $this->parent->user->notifications()->first();

        $this->assertNotNull($notification);

        $payload = $notification->data;
        $this->assertSame('bus_stop_arrival', $payload['type']);
        $this->assertSame('Baneshwor Chowk', $payload['stop_name']);
    }

    public function test_departs_stop_once_bus_leaves(): void
    {
        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6993300,
            85.3392200,
            0.0,
        );

        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.7600000,
            85.4000000,
            40.0,
        );

        $this->assertDatabaseHas('trip_stop_arrivals', [
            'trip_id' => $this->trip->id,
            'status' => 'departed',
        ]);
    }

    public function test_with_stop_context_reports_arrived_and_next_stop(): void
    {
        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6993300,
            85.3392200,
            0.0,
        );

        $payload = app(StopArrivalService::class)->withStopContext(
            $this->bus,
            [
                'latitude' => 27.6993300,
                'longitude' => 85.3392200,
                'speed_kmh' => 0.0,
            ],
        );

        $this->assertSame($this->route->id, $payload['route_id']);
        $this->assertSame('Baneshwor Chowk', $payload['arrived_stop']['name']);
        $this->assertSame(1, $payload['arrived_stop']['stop_order']);
        $this->assertSame('New Baneshwor', $payload['next_stop']['name']);
        $this->assertFalse($payload['is_at_final_stop']);
    }

    public function test_next_stop_null_when_at_final_stop(): void
    {
        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6993300,
            85.3392200,
            0.0,
        );

        app(StopArrivalService::class)->detectForBus(
            $this->bus,
            27.6958000,
            85.3348000,
            0.0,
        );

        $payload = app(StopArrivalService::class)->withStopContext(
            $this->bus,
            ['latitude' => 27.6958000, 'longitude' => 85.3348000, 'speed_kmh' => 0.0],
        );

        $this->assertTrue($payload['is_at_final_stop']);
        $this->assertNull($payload['next_stop']);
    }
}
