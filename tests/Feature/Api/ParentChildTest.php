<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParentChildTest extends TestCase
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
            'code' => 'SCH-CHILD-1',
            'email' => 'child@brightfuture.com',
            'phone' => '9800000300',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Hari Bahadur',
            'email' => 'hari.child@example.com',
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
            'employee_id' => 'DR-CHILD-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000301',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-CHILD-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $route = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route 1',
            'route_code' => 'RT-CHILD-1',
            'start_location' => 'Chabahil',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        $this->bus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'CHILD-BUS-1',
            'registration_number' => 'BA CHILD-BUS-1',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $this->bus->drivers()->attach($driver->id);
        $this->bus->routes()->attach($route->id);
    }

    private function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-CHILD-'.uniqid(),
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

    public function test_parent_can_list_children_with_bus_and_today_attendance(): void
    {
        $this->makeStudent();
        $this->makeStudent(['first_name' => 'Rita', 'roll_no' => '2']);

        Sanctum::actingAs($this->parentUser);

        $response = $this->getJson('/api/v1/parent/children');

        $response->assertOk()
            ->assertJsonPath('message', 'Parent children data.')
            ->assertJsonPath('data.children_count', 2)
            ->assertJsonCount(2, 'data.children')
            ->assertJsonPath('data.children.0.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.children.0.bus.bus_number', 'CHILD-BUS-1')
            ->assertJsonPath('data.children.0.bus.drivers.0.name', 'Ramesh Sharma')
            ->assertJsonPath('data.children.0.today_attendance.next_action.key', 'picked_up_home')
            ->assertJsonStructure([
                'message',
                'data' => [
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
                            'is_active',
                            'bus' => [
                                'id',
                                'bus_number',
                                'registration_number',
                                'status',
                                'routes',
                                'drivers' => [['id', 'name', 'phone']],
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

    public function test_index_returns_empty_children_when_parent_has_none(): void
    {
        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children')
            ->assertOk()
            ->assertJsonPath('data.children_count', 0)
            ->assertJsonCount(0, 'data.children');
    }

    public function test_parent_can_view_child_detail(): void
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

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id)
            ->assertOk()
            ->assertJsonPath('message', 'Parent child data.')
            ->assertJsonPath('data.student.full_name', 'Sita Bahadur')
            ->assertJsonPath('data.student.school.name', 'Bright Future School')
            ->assertJsonPath('data.bus.bus_number', 'CHILD-BUS-1')
            ->assertJsonPath('data.bus.routes.0.name', 'Route 1')
            ->assertJsonPath('data.today_attendance.home_to_school.status', 'completed')
            ->assertJsonPath('data.today_attendance.next_action.key', 'picked_up_school')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'student' => [
                        'id',
                        'admission_no',
                        'first_name',
                        'last_name',
                        'full_name',
                        'gender',
                        'grade',
                        'section',
                        'roll_no',
                        'date_of_birth',
                        'photo',
                        'pickup_location',
                        'drop_location',
                        'pickup_latitude',
                        'pickup_longitude',
                        'drop_latitude',
                        'drop_longitude',
                        'is_active',
                        'school' => ['id', 'name', 'address'],
                    ],
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
                        'routes' => [['id', 'name', 'route_code', 'start_location', 'end_location']],
                        'drivers' => [['id', 'name', 'phone']],
                    ],
                    'today_attendance' => [
                        'home_to_school' => ['check_in_at', 'check_out_at', 'status'],
                        'school_to_home' => ['check_in_at', 'check_out_at', 'status'],
                        'completed',
                        'next_action',
                    ],
                ],
            ]);
    }

    public function test_child_detail_returns_null_bus_when_unassigned(): void
    {
        $student = $this->makeStudent(['bus_id' => null]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id)
            ->assertOk()
            ->assertJsonPath('data.bus', null);
    }

    public function test_parent_can_view_child_attendance_history(): void
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
            'check_out_at' => now()->setTime(16, 10, 0),
            'marked_by' => $this->parentUser->id,
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/history')
            ->assertOk()
            ->assertJsonPath('message', 'Parent child attendance history.')
            ->assertJsonPath('data.total_records', 2)
            ->assertJsonCount(2, 'data.records')
            ->assertJsonPath('data.records.0.trip_label', 'School to Home (Drop)')
            ->assertJsonPath('data.records.0.status', 'completed')
            ->assertJsonPath('data.records.0.marked_by.name', $this->parentUser->name)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'student' => ['id', 'full_name', 'grade', 'section', 'photo'],
                    'from',
                    'to',
                    'total_records',
                    'records' => [
                        '*' => [
                            'id',
                            'date',
                            'trip',
                            'trip_label',
                            'check_in_at',
                            'check_out_at',
                            'status',
                            'marked_by',
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'last_page', 'total', 'from', 'to'],
                ],
            ]);
    }

    public function test_child_history_respects_from_and_to_date_filters(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now()->subDays(45),
            'check_in_at' => now()->subDays(45)->setTime(7, 15, 0),
            'check_out_at' => now()->subDays(45)->setTime(8, 0, 0),
            'marked_by' => $this->parentUser->id,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now()->subDays(3),
            'check_in_at' => now()->subDays(3)->setTime(7, 15, 0),
            'check_out_at' => now()->subDays(3)->setTime(8, 0, 0),
            'marked_by' => $this->parentUser->id,
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/history')
            ->assertOk()
            ->assertJsonPath('data.total_records', 1);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/history'
            .'?from='.now()->subDays(50)->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertJsonPath('data.total_records', 2);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/history'
            .'?from='.now()->subDays(10)->toDateString().'&to='.now()->subDays(5)->toDateString())
            ->assertOk()
            ->assertJsonPath('data.total_records', 0);
    }

    public function test_history_rejects_invalid_date_range(): void
    {
        $student = $this->makeStudent();

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$student->id.'/history'
            .'?from='.now()->toDateString().'&to='.now()->subDays(5)->toDateString())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }

    public function test_parent_cannot_view_another_parents_child(): void
    {
        $otherParentUser = User::factory()->create();
        $otherParentUser->assignRole('Parent');
        $otherParent = ParentProfile::create([
            'user_id' => $otherParentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Other Parent',
            'phone' => '9800000302',
            'address' => 'Kathmandu',
        ]);

        $otherStudent = Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $otherParent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-CHILD-OTHER-'.uniqid(),
            'first_name' => 'Gita',
            'last_name' => 'Sharma',
            'date_of_birth' => '2011-01-01',
            'gender' => 'Female',
            'grade' => '6',
            'pickup_location' => 'Boudha',
            'drop_location' => 'School',
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/'.$otherStudent->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to view this student.');

        $this->getJson('/api/v1/parent/children/'.$otherStudent->id.'/history')
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to view this student.');
    }

    public function test_user_without_parent_profile_gets_404(): void
    {
        $student = $this->makeStudent();

        $user = User::factory()->create();
        $user->assignRole('Parent');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/parent/children')->assertNotFound();
        $this->getJson('/api/v1/parent/children/'.$student->id)->assertNotFound();
        $this->getJson('/api/v1/parent/children/'.$student->id.'/history')->assertNotFound();
    }

    public function test_non_parent_user_is_forbidden(): void
    {
        $student = $this->makeStudent();

        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        Sanctum::actingAs($driverUser);

        $this->getJson('/api/v1/parent/children')->assertForbidden();
        $this->getJson('/api/v1/parent/children/'.$student->id)->assertForbidden();
        $this->getJson('/api/v1/parent/children/'.$student->id.'/history')->assertForbidden();
    }

    public function test_show_returns_clean_404_for_nonexistent_student(): void
    {
        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_history_returns_clean_404_for_nonexistent_student(): void
    {
        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/parent/children/999999/history')
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }
}
