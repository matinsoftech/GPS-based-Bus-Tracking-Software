<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParentDashboardTest extends TestCase
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

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-PARENT-1',
            'email' => 'parent@brightfuture.com',
            'phone' => '9800000100',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Hari Bahadur',
            'email' => 'hari@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->parentUser->assignRole('Parent');

        $this->parent = ParentProfile::create([
            'user_id' => $this->parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9812345678',
            'alternate_phone' => '9812345679',
            'address' => 'Chabahil, Kathmandu',
            'occupation' => 'Engineer',
        ]);

        $driverUser = User::factory()->create();
        $driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR-PARENT-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000101',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-PARENT-1',
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
            'bus_number' => 'PARENT-BUS-1',
            'registration_number' => 'BA PARENT-BUS-1',
            'capacity' => 40,
            'status' => 'Active',
        ]);
    }

    private function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-PARENT-'.uniqid(),
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

    public function test_parent_can_view_dashboard_with_children(): void
    {
        $this->makeStudent();
        $this->makeStudent(['first_name' => 'Rita', 'roll_no' => '2']);

        Sanctum::actingAs($this->parentUser);

        $response = $this->getJson('/api/v1/parent/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.children_count', 2)
            ->assertJsonCount(2, 'data.children')
            ->assertJsonPath('data.children.0.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.children.0.bus.bus_number', 'PARENT-BUS-1')
            ->assertJsonPath('data.children.0.bus.driver.name', 'Ramesh Sharma')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'parent' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'name',
                        'school' => ['id', 'name', 'address'],
                    ],
                    'children_count',
                    'children' => [
                        '*' => [
                            'id',
                            'admission_no',
                            'first_name',
                            'last_name',
                            'full_name',
                            'gender',
                            'grade',
                            'section',
                            'roll_no',
                            'photo',
                            'pickup_location',
                            'drop_location',
                            'bus' => [
                                'id',
                                'bus_number',
                                'registration_number',
                                'status',
                                'route',
                                'driver' => ['id', 'name', 'phone'],
                            ],
                            'today_attendance' => [
                                'home_to_school' => ['check_in_at', 'check_out_at', 'status'],
                                'school_to_home' => ['check_in_at', 'check_out_at', 'status'],
                                'completed',
                                'next_action',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_dashboard_shows_today_attendance_status(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now(),
            'check_in_at' => now()->setTime(7, 15, 0),
            'check_out_at' => now()->setTime(8, 0, 0),
            'marked_by' => $this->parentUser->id,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'school_to_home',
            'date' => now(),
            'check_in_at' => now()->setTime(15, 30, 0),
            'marked_by' => $this->parentUser->id,
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/dashboard')
            ->assertOk()
            ->assertJsonPath('data.children.0.today_attendance.home_to_school.status', 'completed')
            ->assertJsonPath('data.children.0.today_attendance.school_to_home.status', 'checked_in')
            ->assertJsonPath('data.children.0.today_attendance.completed', false)
            ->assertJsonPath('data.children.0.today_attendance.next_action.key', 'dropped_at_home');
    }

    public function test_dashboard_shows_not_started_when_child_has_no_records(): void
    {
        $this->makeStudent();

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/dashboard')
            ->assertOk()
            ->assertJsonPath('data.children.0.today_attendance.home_to_school.status', 'not_checked_in')
            ->assertJsonPath('data.children.0.today_attendance.school_to_home.status', 'not_checked_in')
            ->assertJsonPath('data.children.0.today_attendance.completed', false)
            ->assertJsonPath('data.children.0.today_attendance.next_action.key', 'picked_up_home');
    }

    public function test_dashboard_returns_empty_children_when_parent_has_none(): void
    {
        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/dashboard')
            ->assertOk()
            ->assertJsonPath('data.children_count', 0)
            ->assertJsonCount(0, 'data.children');
    }

    public function test_user_without_parent_profile_gets_404(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Parent');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/parent/dashboard')->assertNotFound();
        $this->getJson('/api/v1/parent/profile')->assertNotFound();
    }

    public function test_non_parent_user_is_forbidden(): void
    {
        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        Sanctum::actingAs($driverUser);

        $this->getJson('/api/v1/parent/dashboard')->assertForbidden();
    }

    public function test_parent_can_view_profile(): void
    {
        $this->makeStudent();

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Hari Bahadur')
            ->assertJsonPath('data.phone', '9812345678')
            ->assertJsonPath('data.alternate_phone', '9812345679')
            ->assertJsonPath('data.role', 'Parent')
            ->assertJsonPath('data.school.name', 'Bright Future School')
            ->assertJsonPath('data.children_count', 1)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'alternate_phone',
                    'name',
                    'address',
                    'occupation',
                    'role',
                    'status',
                    'school' => ['id', 'name', 'address'],
                    'children_count',
                ],
            ]);
    }
}
