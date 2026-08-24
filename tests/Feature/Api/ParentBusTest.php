<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParentBusTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $parentUser;

    private ParentProfile $parent;

    private Driver $driver;

    private Route $route;

    private Bus $bus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        config(['gps.base_url' => 'https://gps.example.com']);
        config(['gps.cache_ttl' => 30]);

        Cache::flush();

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-BUS-1',
            'email' => 'bus@brightfuture.com',
            'phone' => '9800000200',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Hari Bahadur',
            'email' => 'hari.bus@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->parentUser->assignRole('Parent');

        $this->parent = ParentProfile::create([
            'user_id' => $this->parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9812345678',
            'address' => 'Chabahil, Kathmandu',
        ]);

        $driverUser = User::factory()->create();
        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR-BUS-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000201',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-BUS-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $this->route = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route 1',
            'route_code' => 'RT-BUS-1',
            'start_location' => 'Chabahil',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        RouteStop::create([
            'route_id' => $this->route->id,
            'name' => 'Chabahil',
            'latitude' => 27.7172,
            'longitude' => 85.3409,
            'stop_order' => 1,
            'pickup_time' => '07:00:00',
        ]);

        RouteStop::create([
            'route_id' => $this->route->id,
            'name' => 'School',
            'latitude' => 27.71,
            'longitude' => 85.32,
            'stop_order' => 2,
            'drop_time' => '15:30:00',
        ]);

        $this->bus = Bus::create([
            'school_id' => $this->school->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driver->id,
            'bus_number' => 'PARENT-BUS-1',
            'registration_number' => 'BA PARENT-BUS-1',
            'capacity' => 40,
            'gps_device_id' => '123456789012345',
            'status' => 'Active',
        ]);
    }

    private function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-BUS-'.uniqid(),
            'first_name' => 'Sita',
            'last_name' => 'Bahadur',
            'date_of_birth' => '2012-05-10',
            'gender' => 'Female',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Chabahil',
            'drop_location' => 'School',
            'is_active' => true,
        ], $overrides));
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

    public function test_parent_can_view_child_bus_with_route_stops_driver_and_live_location(): void
    {
        $student = $this->makeStudent();

        $this->fakeLiveTracking([
            [
                'imei' => '123456789012345',
                'asset_name' => 'Bus 1',
                'latitude' => 27.7172,
                'longitude' => 85.324,
                'speed_kmh' => 45.0,
                'course' => 90,
                'status' => 'moving',
                'status_label' => 'Moving',
                'status_color' => '#22c55e',
                'is_moving' => true,
                'is_online' => true,
                'gps_time' => now()->toDateTimeString(),
                'last_updated_at' => now()->toDateTimeString(),
            ],
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/bus')
            ->assertOk()
            ->assertJsonPath('message', 'Parent child bus data.')
            ->assertJsonPath('data.student.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.bus.bus_number', 'PARENT-BUS-1')
            ->assertJsonPath('data.bus.driver.name', 'Ramesh Sharma')
            ->assertJsonPath('data.bus.route.name', 'Route 1')
            ->assertJsonCount(2, 'data.bus.route.stops')
            ->assertJsonPath('data.bus.route.stops.0.name', 'Chabahil')
            ->assertJsonPath('data.live_location.imei', '123456789012345')
            ->assertJsonPath('data.live_location.latitude', 27.7172)
            ->assertJsonPath('data.live_location.longitude', 85.324)
            ->assertJsonPath('data.live_location.speed_kmh', 45)
            ->assertJsonPath('data.live_location.status_label', 'Moving')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'student' => ['id', 'full_name', 'grade', 'section', 'photo', 'pickup_location', 'drop_location'],
                    'bus' => [
                        'id',
                        'bus_number',
                        'registration_number',
                        'make',
                        'model',
                        'year',
                        'capacity',
                        'fuel_type',
                        'status',
                        'driver' => ['id', 'name', 'phone'],
                        'route' => [
                            'id',
                            'name',
                            'route_code',
                            'start_location',
                            'end_location',
                            'stops' => [
                                '*' => ['id', 'name', 'latitude', 'longitude', 'stop_order', 'pickup_time', 'drop_time'],
                            ],
                        ],
                        'school' => ['id', 'name', 'address'],
                    ],
                    'live_location',
                ],
            ]);
    }

    public function test_bus_without_gps_device_shows_null_live_location(): void
    {
        $this->bus->update(['gps_device_id' => null]);

        $student = $this->makeStudent();

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/bus')
            ->assertOk()
            ->assertJsonPath('data.bus.bus_number', 'PARENT-BUS-1')
            ->assertJsonPath('data.live_location', null);
    }

    public function test_parent_cannot_view_another_parents_child(): void
    {
        $otherParentUser = User::factory()->create();
        $otherParentUser->assignRole('Parent');
        $otherParent = ParentProfile::create([
            'user_id' => $otherParentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Other Parent',
            'phone' => '9800000202',
            'address' => 'Kathmandu',
        ]);

        $otherStudent = Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $otherParent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-BUS-OTHER-'.uniqid(),
            'first_name' => 'Gita',
            'last_name' => 'Sharma',
            'date_of_birth' => '2011-01-01',
            'gender' => 'Female',
            'grade' => '6',
            'pickup_location' => 'Boudha',
            'drop_location' => 'School',
            'is_active' => true,
        ]);

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$otherStudent->id.'/bus')
            ->assertForbidden()
            ->assertJsonPath('message', "You are not authorized to view this student's bus.");
    }

    public function test_child_without_bus_returns_404(): void
    {
        $student = $this->makeStudent(['bus_id' => null]);

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/bus')
            ->assertNotFound()
            ->assertJsonPath('message', 'Bus not found for this child.');
    }

    public function test_user_without_parent_profile_gets_404(): void
    {
        $student = $this->makeStudent();

        $user = User::factory()->create();
        $user->assignRole('Parent');

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/bus')
            ->assertNotFound()
            ->assertJsonPath('message', 'Parent profile not found.');
    }

    public function test_non_parent_user_is_forbidden(): void
    {
        $student = $this->makeStudent();

        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        Sanctum::actingAs($driverUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/bus')
            ->assertForbidden();
    }

    public function test_bus_returns_clean_404_for_nonexistent_student(): void
    {
        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/999999/bus')
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }
}
