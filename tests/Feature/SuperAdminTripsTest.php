<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\TripEndedNotification;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SuperAdminTripsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_all_trips_across_all_schools(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolB = $this->createSchool('Moonlight School', 'SCH-B');

        $this->makeTrip($schoolA);
        $this->makeTrip($schoolB);

        $response = $this->actingAs($superAdmin)->get(route('trips.index'));

        $response->assertOk()
            ->assertSee('Sunrise Academy')
            ->assertSee('Moonlight School')
            ->assertSee('Trip History');
    }

    public function test_super_admin_can_filter_trips_by_school(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolB = $this->createSchool('Moonlight School', 'SCH-B');

        $tripA = $this->makeTrip($schoolA);
        $tripB = $this->makeTrip($schoolB);

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['school_id' => $schoolA->id]));

        $response->assertOk()
            ->assertSee('Sunrise Academy')
            ->assertSee($tripA->bus->bus_number)
            ->assertDontSee($tripB->bus->bus_number);
    }

    public function test_super_admin_can_end_in_progress_trip_of_any_school(): void
    {
        Notification::fake();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');

        $principal = User::factory()->create([
            'name' => 'Principal A',
            'email' => 'principal@example.com',
            'school_id' => $school->id,
        ]);
        $principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $principal->id,
            'school_id' => $school->id,
            'name' => 'Principal A',
            'phone' => '9800000000',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $parentUser = User::factory()->create(['school_id' => $school->id]);
        $parentUser->assignRole('Parent');

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $school->id,
            'name' => 'Parent A',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
        ]);

        $trip = $this->makeTrip($school, $parent);

        $response = $this->actingAs($superAdmin)->post(route('trips.end', $trip));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);
        $this->assertNotNull($trip->fresh()->ended_at);

        Notification::assertSentTo($parentUser, TripEndedNotification::class);
        Notification::assertSentTo($principal, TripEndedNotification::class);
    }

    public function test_super_admin_cannot_end_an_already_completed_trip(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $trip = $this->makeTrip($school);

        $trip->update([
            'status' => Trip::STATUS_COMPLETED,
            'ended_at' => now(),
        ]);

        $response = $this->actingAs($superAdmin)->post(route('trips.end', $trip));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);
    }

    public function test_non_super_admin_cannot_access_trips_page(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        $response = $this->actingAs($driverUser)->get(route('trips.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_filter_trips_by_status_bus_driver_route_and_date_range(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');

        $tripA = $this->makeTrip($schoolA, null, [
            'status' => Trip::STATUS_COMPLETED,
            'started_at' => now()->subDays(5)->setTime(8, 0, 0),
            'ended_at' => now()->subDays(5)->setTime(9, 0, 0),
        ]);
        $tripB = $this->makeTrip($schoolA, null, [
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now()->subDay()->setTime(8, 0, 0),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['status' => 'completed']));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['bus_id' => $tripA->bus_id]));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['driver_id' => $tripA->driver_id]));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['route_id' => $tripA->route_id]));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));

        $params = ['from' => now()->subDays(6)->toDateString(), 'to' => now()->subDays(2)->toDateString()];
        $response = $this->actingAs($superAdmin)->get(route('trips.index', $params));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));
    }

    public function test_trip_filters_reject_invalid_values(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin)->get(route('trips.index', ['status' => 'cancelled']))
            ->assertSessionHasErrors('status');

        $this->actingAs($superAdmin)->get(route('trips.index', ['from' => '2026-09-10', 'to' => '2026-09-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_super_admin_can_search_trips_by_keyword(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');

        $tripA = $this->makeTrip($schoolA, null, [
            'status' => Trip::STATUS_COMPLETED,
            'started_at' => now()->subDays(5)->setTime(8, 0, 0),
            'ended_at' => now()->subDays(5)->setTime(9, 0, 0),
        ]);
        $tripB = $this->makeTrip($schoolA, null, [
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now()->subDay()->setTime(8, 0, 0),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['search' => $tripA->bus->bus_number]));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number);
        $this->assertSame(1, substr_count($response->getContent(), $tripB->bus->bus_number));

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['search' => $tripB->route->route_code]));
        $response->assertOk()
            ->assertSee($tripB->bus->bus_number)
            ->assertSee($tripB->route->route_code);
        $this->assertSame(1, substr_count($response->getContent(), $tripA->bus->bus_number));

        $response = $this->actingAs($superAdmin)->get(route('trips.index', ['search' => 'SCH-A']));
        $response->assertOk()
            ->assertSee($tripA->bus->bus_number)
            ->assertSee($tripB->bus->bus_number);
    }

    private function makeTrip(School $school, ?ParentProfile $parent = null, array $overrides = []): Trip
    {
        $driver = $this->createDriver($school);
        $bus = $this->createBus($school);
        $route = $this->createRoute($school);

        if ($parent) {
            $this->createStudent($school, $parent, $route);
        }

        return Trip::create(array_merge([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'school_id' => $school->id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now()->subHour(),
        ], $overrides));
    }

    private function createSchool(string $name, string $code): School
    {
        $school = School::create([
            'name' => $name,
            'code' => $code,
            'email' => strtolower($code).'@school.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal',
            'status' => 'active',
        ]);

        $this->activateSubscription($school);

        return $school;
    }

    private function createDriver(School $school): Driver
    {
        $user = User::factory()->create();

        return Driver::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'employee_id' => 'EMP-'.uniqid(),
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-'.uniqid(),
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $user->id,
        ]);
    }

    private function createBus(School $school): Bus
    {
        return Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'BUS-'.uniqid(),
            'registration_number' => 'BA '.uniqid(),
            'capacity' => 40,
            'status' => 'Active',
        ]);
    }

    private function createRoute(School $school): Route
    {
        return Route::create([
            'school_id' => $school->id,
            'name' => 'Route A',
            'route_code' => 'R-'.uniqid(),
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'is_active' => true,
        ]);
    }

    private function createStudent(School $school, ParentProfile $parent, Route $route): Student
    {
        $student = Student::create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => 'Alice',
            'last_name' => 'Child',
            'date_of_birth' => '2015-05-10',
            'gender' => 'Female',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Kathmandu',
            'drop_location' => 'School',
            'is_active' => true,
        ]);

        $student->routes()->sync([$route->id]);

        return $student;
    }
}