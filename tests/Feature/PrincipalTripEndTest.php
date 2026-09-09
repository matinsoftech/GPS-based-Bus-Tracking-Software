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

class PrincipalTripEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_end_in_progress_trip_in_their_school(): void
    {
        Notification::fake();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

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

        $driver = $this->createDriver($school);
        $bus = $this->createBus($school);
        $route = $this->createRoute($school);
        $student = $this->createStudent($school, $parent, $route);

        $trip = Trip::create([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'school_id' => $school->id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($principal)->post(route('principal.trips.end', $trip));

        $response->assertRedirect(route('principal.trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);
        $this->assertNotNull($trip->fresh()->ended_at);

        Notification::assertSentTo($parentUser, TripEndedNotification::class);
        Notification::assertSentTo($principal, TripEndedNotification::class);
    }

    public function test_school_admin_cannot_end_trip_of_another_school(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolB = $this->createSchool('Moonlight School', 'SCH-B');

        $principal = User::factory()->create([
            'name' => 'Principal A',
            'email' => 'principal@example.com',
            'school_id' => $schoolA->id,
        ]);
        $principal->assignRole('School Admin');

        $driver = $this->createDriver($schoolB);
        $bus = $this->createBus($schoolB);
        $route = $this->createRoute($schoolB);

        $trip = Trip::create([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'school_id' => $schoolB->id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_IN_PROGRESS,
            'started_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($principal)->post(route('principal.trips.end', $trip));

        $response->assertForbidden();

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => Trip::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_school_admin_cannot_end_an_already_completed_trip(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');

        $principal = User::factory()->create([
            'name' => 'Principal A',
            'email' => 'principal@example.com',
            'school_id' => $school->id,
        ]);
        $principal->assignRole('School Admin');

        $driver = $this->createDriver($school);
        $bus = $this->createBus($school);
        $route = $this->createRoute($school);

        $trip = Trip::create([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_id' => $route->id,
            'school_id' => $school->id,
            'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
            'status' => Trip::STATUS_COMPLETED,
            'started_at' => now()->subDay(),
            'ended_at' => now()->subDay()->addHour(),
        ]);

        $response = $this->actingAs($principal)->post(route('principal.trips.end', $trip));

        $response->assertRedirect(route('principal.trips.index'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);
    }

    private function createSchool(string $name, string $code): School
    {
        return School::create([
            'name' => $name,
            'code' => $code,
            'email' => strtolower($code).'@school.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal',
            'status' => 'active',
        ]);
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