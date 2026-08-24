<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrincipalDashboardTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private User $principal;

    private SchoolAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        config(['gps.base_url' => 'https://gps.example.com']);
        config(['gps.cache_ttl' => 30]);

        Cache::flush();

        $this->school = School::create([
            'name' => 'Sunrise Valley School',
            'code' => 'SCH-PR-1',
            'email' => 'sunrise@example.com',
            'phone' => '9800000500',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->otherSchool = School::create([
            'name' => 'Other School',
            'code' => 'SCH-PR-2',
            'email' => 'other@example.com',
            'phone' => '9800000501',
            'address' => 'Lalitpur',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Principal Alpha',
            'email' => 'principal@sunrise.example.com',
            'school_id' => $this->school->id,
        ]);
        $this->principal->assignRole('School Admin');

        $this->admin = SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Alpha',
            'phone' => '9812345678',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->fakeLiveTracking([]);
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

    private function makeDriver(string $employeeId, string $status): Driver
    {
        $user = User::factory()->create();

        return Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000502',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-PR-'.$employeeId,
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => $status,
            'created_by' => $user->id,
        ]);
    }

    private function makeBus(string $busNumber, ?int $driverId = null, string $status = 'Active'): Bus
    {
        return Bus::create([
            'school_id' => $this->school->id,
            'driver_id' => $driverId,
            'bus_number' => $busNumber,
            'registration_number' => 'BA '.$busNumber,
            'capacity' => 40,
            'status' => $status,
        ]);
    }

    private function makeParent(): ParentProfile
    {
        $user = User::factory()->create();
        $user->assignRole('Parent');

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Sharma',
            'phone' => '9812345678',
            'address' => 'Chabahil, Kathmandu',
        ]);
    }

    private function makeStudent(int $busId, ParentProfile $parent): Student
    {
        return Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'bus_id' => $busId,
            'admission_no' => 'ADM-PR-'.uniqid(),
            'first_name' => 'Sita',
            'last_name' => 'Sharma',
            'date_of_birth' => '2012-05-10',
            'gender' => 'Female',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Chabahil',
            'drop_location' => 'School',
            'is_active' => true,
        ]);
    }

    private function makeRoute(string $name, string $code, bool $isActive): Route
    {
        return Route::create([
            'school_id' => $this->school->id,
            'name' => $name,
            'route_code' => $code,
            'start_location' => 'Chabahil',
            'end_location' => 'School',
            'estimated_distance' => 5,
            'estimated_duration' => 20,
            'is_active' => $isActive,
        ]);
    }

    private function seedSchoolData(): array
    {
        $driver = $this->makeDriver('PR-EMP-1', 'Active');
        $this->makeDriver('PR-EMP-2', 'Suspended');

        $bus = $this->makeBus('PR-BUS-1', $driver->id);
        $this->makeBus('PR-BUS-2', null, 'Maintenance');
        $this->makeBus('PR-BUS-3', null, 'Inactive');

        $parent = $this->makeParent();
        $student = $this->makeStudent($bus->id, $parent);

        $this->makeRoute('Route A', 'R-PR-1', true);
        $this->makeRoute('Route B', 'R-PR-2', false);

        $otherParent = $this->makeParent();
        $otherBus = Bus::create([
            'school_id' => $this->otherSchool->id,
            'bus_number' => 'OTH-BUS-1',
            'registration_number' => 'BA OTH-BUS-1',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $this->makeStudentFor($otherBus, $this->otherSchool, $otherParent);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $bus->id,
            'trip' => 'home_to_school',
            'date' => today()->toDateString(),
            'check_in_at' => now()->subHour(),
            'marked_by' => $this->principal->id,
        ]);

        return ['bus' => $bus, 'student' => $student];
    }

    private function makeStudentFor(Bus $bus, School $school, ParentProfile $parent): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'bus_id' => $bus->id,
            'admission_no' => 'ADM-OTH-'.uniqid(),
            'first_name' => 'Rita',
            'last_name' => 'Sharma',
            'date_of_birth' => '2013-05-10',
            'gender' => 'Female',
            'grade' => '4',
            'section' => 'B',
            'roll_no' => '1',
            'pickup_location' => 'Balkumari',
            'drop_location' => 'School',
            'is_active' => true,
        ]);
    }

    public function test_principal_can_view_dashboard_stats_and_live_fleet(): void
    {
        $this->seedSchoolData();

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/principal/dashboard')
            ->assertOk()
            ->assertJsonPath('message', 'Principal dashboard data.')
            ->assertJsonPath('data.principal.name', 'Principal Alpha')
            ->assertJsonPath('data.principal.role', 'School Admin')
            ->assertJsonPath('data.school.id', $this->school->id)
            ->assertJsonPath('data.stats.total_buses', 3)
            ->assertJsonPath('data.stats.active_buses', 1)
            ->assertJsonPath('data.stats.maintenance_buses', 1)
            ->assertJsonPath('data.stats.inactive_buses', 1)
            ->assertJsonPath('data.stats.total_drivers', 2)
            ->assertJsonPath('data.stats.active_drivers', 1)
            ->assertJsonPath('data.stats.suspended_drivers', 1)
            ->assertJsonPath('data.stats.total_students', 1)
            ->assertJsonPath('data.stats.active_students', 1)
            ->assertJsonPath('data.stats.total_routes', 2)
            ->assertJsonPath('data.stats.active_routes', 1)
            ->assertJsonPath('data.stats.today_attendance.total', 1)
            ->assertJsonPath('data.stats.today_attendance.checked_in', 1)
            ->assertJsonPath('data.live_fleet.summary.total', 3)
            ->assertJsonPath('data.live_fleet.school.name', 'Sunrise Valley School')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'principal' => ['id', 'name', 'email', 'role', 'status'],
                    'school' => ['id', 'name', 'code', 'address', 'phone', 'principal_name', 'status'],
                    'stats' => [
                        'total_buses',
                        'active_buses',
                        'maintenance_buses',
                        'inactive_buses',
                        'total_drivers',
                        'active_drivers',
                        'suspended_drivers',
                        'total_students',
                        'active_students',
                        'total_routes',
                        'active_routes',
                        'today_attendance' => ['total', 'checked_in'],
                    ],
                    'live_fleet' => [
                        'buses',
                        'routes',
                        'summary' => [
                            'total',
                            'active',
                            'maintenance',
                            'inactive',
                            'moving',
                            'stopped',
                            'routes_running',
                        ],
                        'school',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_dashboard_stats_are_scoped_to_own_school_only(): void
    {
        $this->seedSchoolData();

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/principal/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.total_students', 1)
            ->assertJsonPath('data.stats.total_buses', 3)
            ->assertJsonPath('data.live_fleet.summary.total', 3)
            ->assertJsonPath('data.live_fleet.school.name', 'Sunrise Valley School');
    }

    public function test_dashboard_without_school_resolution_returns_404(): void
    {
        $user = User::factory()->create([
            'name' => 'Floating Admin',
            'email' => 'floating@example.com',
            'school_id' => null,
        ]);
        $user->assignRole('School Admin');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/principal/dashboard')
            ->assertNotFound()
            ->assertJsonPath('message', 'Principal profile not found.');
    }

    public function test_non_school_admin_user_is_forbidden(): void
    {
        $parent = User::factory()->create();
        $parent->assignRole('Parent');

        Sanctum::actingAs($parent);

        $this->getJson('/api/v1/principal/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this resource.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
        $this->getJson('/api/v1/principal/profile')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this resource.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_school_admin_without_dashboard_permission_is_forbidden(): void
    {
        $user = User::factory()->create([
            'school_id' => $this->school->id,
        ]);
        $user->assignRole('School Admin');
        Role::findByName('School Admin')->revokePermissionTo('dashboard.view');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/principal/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this resource.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_principal_can_view_profile(): void
    {
        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/principal/profile')
            ->assertOk()
            ->assertJsonPath('message', 'Principal profile data.')
            ->assertJsonPath('data.name', 'Principal Alpha')
            ->assertJsonPath('data.email', 'principal@sunrise.example.com')
            ->assertJsonPath('data.phone', '9812345678')
            ->assertJsonPath('data.designation', 'Principal')
            ->assertJsonPath('data.role', 'School Admin')
            ->assertJsonPath('data.school.id', $this->school->id)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'designation',
                    'address',
                    'role',
                    'status',
                    'school' => ['id', 'name', 'address'],
                ],
            ]);
    }

    public function test_profile_without_school_admin_record_returns_404(): void
    {
        $user = User::factory()->create([
            'school_id' => $this->school->id,
        ]);
        $user->assignRole('School Admin');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/principal/profile')
            ->assertNotFound()
            ->assertJsonPath('message', 'Principal profile not found.');
    }
}
