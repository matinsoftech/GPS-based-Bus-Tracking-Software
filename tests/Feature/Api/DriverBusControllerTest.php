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

    public function test_stops_include_assigned_students_for_each_stop(): void
    {
        $stopOne = $this->makeStop('Maitighar', 1, '07:10');
        $stopTwo = $this->makeStop('Gaushala', 2, '07:25');

        $studentB = $this->makeStudent('Bibek', 'Shrestha');
        $studentA = $this->makeStudent('Anita', 'Shrestha');

        $stopOne->students()->attach([$studentB->id, $studentA->id]);
        $stopTwo->students()->attach($studentA->id);

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
                    ],
                ],
            ]);

        $stops = $response->json('data.stops');
        $maitighar = collect($stops)->firstWhere('id', $stopOne->id);
        $gaushala = collect($stops)->firstWhere('id', $stopTwo->id);

        $this->assertSame(['Anita', 'Bibek'], array_column($maitighar['students'], 'first_name'));
        $this->assertCount(1, $gaushala['students']);
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
}