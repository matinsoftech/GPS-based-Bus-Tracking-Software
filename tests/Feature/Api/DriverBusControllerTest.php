<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverBusControllerTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Driver $driver;

    private User $driverUser;

    private Route $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-BUS-API',
            'email' => 'busapi@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create();
        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-BUS-API-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-BUS-API-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);

        $this->route = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route A',
            'route_code' => 'RA',
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        $this->route->drivers()->attach($this->driver->id);

        $this->activateSubscription($this->school);
    }

    private function makeStop(string $name, int $order, ?string $pickupTime = null): RouteStop
    {
        return RouteStop::create([
            'route_id' => $this->route->id,
            'name' => $name,
            'latitude' => 27.7172,
            'longitude' => 85.324,
            'stop_order' => $order,
            'pickup_time' => $pickupTime,
            'is_active' => true,
        ]);
    }

    private function makeStudent(string $firstName, string $lastName): Student
    {
        $parentUser = User::factory()->create();
        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $this->school->id,
            'name' => $firstName.' Parent',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
        ]);

        return Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => '2012-01-01',
            'gender' => 'Male',
            'grade' => '7',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Kathmandu',
            'drop_location' => 'School',
            'is_active' => true,
        ]);
    }

    public function test_stops_returns_stops_in_order(): void
    {
        $this->makeStop('Maitighar', 1, '07:10');
        $this->makeStop('Gaushala', 2, '07:25');

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/routes/'.$this->route->id.'/stops');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Route stops data.')
            ->assertJsonPath('data.route_id', $this->route->id)
            ->assertJsonPath('data.route_name', 'Route A')
            ->assertJsonCount(2, 'data.stops')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'route_id',
                    'route_name',
                    'stops' => [
                        '*' => [
                            'id',
                            'name',
                            'stop_order',
                        ],
                    ],
                ],
            ]);

        $this->assertSame(['Maitighar', 'Gaushala'], array_column($response->json('data.stops'), 'name'));
    }

    public function test_stops_response_is_forbidden_for_unassigned_route(): void
    {
        $otherRoute = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route B',
            'route_code' => 'RB',
            'route_type' => Route::ROUTE_TYPE_SCHOOL_TO_HOME,
            'start_location' => 'School',
            'end_location' => 'Kathmandu',
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/routes/'.$otherRoute->id.'/stops')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not assigned to this route.');
    }

    public function test_stops_response_requires_driver_profile(): void
    {
        $parentUser = User::factory()->create();

        Sanctum::actingAs($parentUser);

        $this->getJson('/api/v1/driver/routes/'.$this->route->id.'/stops')
            ->assertNotFound()
            ->assertJsonPath('message', 'Driver profile not found.');
    }

    public function test_stop_students_returns_assigned_students_for_stop(): void
    {
        $stop = $this->makeStop('Maitighar', 1, '07:10');

        $studentB = $this->makeStudent('Bibek', 'Shrestha');
        $studentA = $this->makeStudent('Anita', 'Shrestha');

        $stop->students()->attach([$studentB->id, $studentA->id]);

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/stops/'.$stop->id.'/students');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Stop students data.')
            ->assertJsonPath('data.stop_id', $stop->id)
            ->assertJsonPath('data.stop_name', 'Maitighar')
            ->assertJsonPath('data.route_id', $this->route->id)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'stop_id',
                    'stop_name',
                    'route_id',
                    'students' => [
                        '*' => [
                            'id',
                            'first_name',
                            'last_name',
                            'parent' => [
                                'user',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertSame(['Anita', 'Bibek'], array_column($response->json('data.students'), 'first_name'));
    }

    public function test_stop_students_returns_empty_list_when_no_students(): void
    {
        $stop = $this->makeStop('Gaushala', 1, '07:25');

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/stops/'.$stop->id.'/students');

        $response
            ->assertOk()
            ->assertJsonPath('data.stop_id', $stop->id)
            ->assertJsonCount(0, 'data.students');
    }

    public function test_stop_students_returns_404_for_missing_stop(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/stops/999999/students')
            ->assertNotFound()
            ->assertJsonPath('message', 'Stop not found.');
    }

    public function test_stop_students_is_forbidden_for_stop_on_unassigned_route(): void
    {
        $otherRoute = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route B',
            'route_code' => 'RB',
            'route_type' => Route::ROUTE_TYPE_SCHOOL_TO_HOME,
            'start_location' => 'School',
            'end_location' => 'Kathmandu',
            'is_active' => true,
        ]);

        $otherStop = RouteStop::create([
            'route_id' => $otherRoute->id,
            'name' => 'Outer Stop',
            'latitude' => 27.7,
            'longitude' => 85.3,
            'stop_order' => 1,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/stops/'.$otherStop->id.'/students')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not assigned to this route.');
    }

    public function test_stop_students_requires_driver_profile(): void
    {
        $parentUser = User::factory()->create();

        Sanctum::actingAs($parentUser);

        $this->getJson('/api/v1/driver/stops/1/students')
            ->assertNotFound()
            ->assertJsonPath('message', 'Driver profile not found.');
    }
}