<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
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

class ParentLiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $parentUser;

    private ParentProfile $parent;

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
            'code' => 'SCH-LIVE-1',
            'email' => 'live@brightfuture.com',
            'phone' => '9800000400',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Hari Bahadur',
            'email' => 'hari.live@example.com',
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
        $driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR-LIVE-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000401',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-LIVE-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $this->bus = Bus::create([
            'school_id' => $this->school->id,
            'driver_id' => $driver->id,
            'bus_number' => 'LIVE-BUS-1',
            'registration_number' => 'BA LIVE-BUS-1',
            'capacity' => 40,
            'gps_device_id' => '123456789012345',
            'status' => 'Active',
        ]);
    }

    private function makeBus(string $busNumber, ?string $imei = null): Bus
    {
        return Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => $busNumber,
            'registration_number' => 'BA '.$busNumber,
            'capacity' => 40,
            'gps_device_id' => $imei,
            'status' => 'Active',
        ]);
    }

    private function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-LIVE-'.uniqid(),
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

    public function test_parent_can_view_live_tracking_for_all_children_buses(): void
    {
        $bus2 = $this->makeBus('LIVE-BUS-2', '222222222222222');

        $this->makeStudent();
        $this->makeStudent(['bus_id' => $bus2->id, 'first_name' => 'Rita', 'roll_no' => '2']);

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
            [
                'imei' => '222222222222222',
                'asset_name' => 'Bus 2',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'speed_kmh' => 0.0,
                'course' => 0,
                'status' => 'stopped',
                'status_label' => 'Stopped',
                'status_color' => '#f59e0b',
                'is_moving' => false,
                'is_online' => true,
                'gps_time' => now()->toDateTimeString(),
                'last_updated_at' => now()->toDateTimeString(),
            ],
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/live-tracking')
            ->assertOk()
            ->assertJsonPath('message', 'Parent live tracking data.')
            ->assertJsonPath('data.children_count', 2)
            ->assertJsonCount(2, 'data.children')
            ->assertJsonPath('data.children.0.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.children.0.bus.bus_number', 'LIVE-BUS-1')
            ->assertJsonPath('data.children.0.live_location.imei', '123456789012345')
            ->assertJsonPath('data.children.0.live_location.latitude', 27.7172)
            ->assertJsonPath('data.children.0.live_location.longitude', 85.324)
            ->assertJsonPath('data.children.0.live_location.speed_kmh', 45)
            ->assertJsonPath('data.children.0.live_location.status_label', 'Moving')
            ->assertJsonPath('data.children.0.live_location.is_moving', true)
            ->assertJsonPath('data.children.1.bus.bus_number', 'LIVE-BUS-2')
            ->assertJsonPath('data.children.1.live_location.imei', '222222222222222')
            ->assertJsonPath('data.children.1.live_location.status_label', 'Stopped')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'children_count',
                    'children' => [
                        '*' => [
                            'id',
                            'full_name',
                            'grade',
                            'section',
                            'photo',
                            'bus' => ['id', 'bus_number', 'registration_number', 'status'],
                            'live_location',
                        ],
                    ],
                ],
            ]);
    }

    public function test_child_without_bus_has_null_live_location(): void
    {
        $this->makeStudent(['bus_id' => null]);

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/live-tracking')
            ->assertOk()
            ->assertJsonPath('data.children.0.bus', null)
            ->assertJsonPath('data.children.0.live_location', null);
    }

    public function test_bus_without_matching_imei_shows_null_live_location(): void
    {
        $this->bus->update(['gps_device_id' => '999999999999999']);
        $this->makeStudent();

        $this->fakeLiveTracking([
            [
                'imei' => '111111111111111',
                'latitude' => 27.7,
                'longitude' => 85.3,
                'is_online' => true,
            ],
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/live-tracking')
            ->assertOk()
            ->assertJsonPath('data.children.0.live_location', null);
    }

    public function test_parent_can_view_single_child_live_tracking(): void
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

        $this->getJson('/api/v1/parent/children/'.$student->id.'/live-tracking')
            ->assertOk()
            ->assertJsonPath('message', 'Parent child live tracking data.')
            ->assertJsonPath('data.student.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.bus.bus_number', 'LIVE-BUS-1')
            ->assertJsonPath('data.live_location.imei', '123456789012345')
            ->assertJsonPath('data.live_location.speed_kmh', 45)
            ->assertJsonPath('data.live_location.status_label', 'Moving')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'student' => ['id', 'full_name', 'grade', 'section', 'photo'],
                    'bus' => ['id', 'bus_number', 'registration_number', 'status'],
                    'live_location',
                ],
            ]);
    }

    public function test_parent_cannot_view_another_parents_child(): void
    {
        $student = $this->makeStudent();

        $otherParentUser = User::factory()->create();
        $otherParentUser->assignRole('Parent');
        ParentProfile::create([
            'user_id' => $otherParentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Other Parent',
            'phone' => '9800000402',
            'address' => 'Kathmandu',
        ]);

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($otherParentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/live-tracking')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to view this student.');
    }

    public function test_user_without_parent_profile_gets_404(): void
    {
        $student = $this->makeStudent();

        $user = User::factory()->create();
        $user->assignRole('Parent');

        $this->fakeLiveTracking([]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/parent/live-tracking')->assertNotFound();
        $this->getJson('/api/v1/parent/children/'.$student->id.'/live-tracking')->assertNotFound();
    }

    public function test_non_parent_user_is_forbidden(): void
    {
        $student = $this->makeStudent();

        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        Sanctum::actingAs($driverUser);

        $this->getJson('/api/v1/parent/live-tracking')->assertForbidden();
        $this->getJson('/api/v1/parent/children/'.$student->id.'/live-tracking')->assertForbidden();
    }

    public function test_show_returns_clean_404_for_nonexistent_student(): void
    {
        $this->fakeLiveTracking([]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/999999/live-tracking')
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }
}
