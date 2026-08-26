<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createSchool(string $code = 'SCH001'): School
    {
        return School::create([
            'name' => "School {$code}",
            'code' => $code,
            'email' => "admin{$code}@example.com",
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'school_id' => null,
        ], $attributes));
    }

    private function createDriver(School $school, User $user, string $suffix = '001'): Driver
    {
        return Driver::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'employee_id' => "DR{$suffix}",
            'first_name' => 'Ramesh',
            'last_name' => "Sharma{$suffix}",
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => "LIC-{$suffix}",
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $user->id,
        ]);
    }

    private function createBus(School $school, string $busNumber, ?Driver $driver = null): Bus
    {
        $bus = Bus::create([
            'school_id' => $school->id,
            'bus_number' => $busNumber,
            'registration_number' => "BA {$busNumber}",
            'capacity' => 40,
            'status' => 'Active',
        ]);

        if ($driver) {
            $bus->drivers()->attach($driver->id);
        }

        return $bus;
    }

    private function createStudent(School $school, Bus $bus, string $admissionNo, ?ParentProfile $parent = null): Student
    {
        $parent ??= $this->createParent($school);

        return Student::create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'bus_id' => $bus->id,
            'admission_no' => $admissionNo,
            'first_name' => 'Aarav',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2014-01-01',
            'gender' => 'Male',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Baneshwor',
            'drop_location' => 'School',
            'is_active' => true,
        ]);
    }

    private function createParent(School $school): ParentProfile
    {
        $user = $this->createUser();
        $user->assignRole('Parent');

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => 'Bishal Shrestha',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
        ]);
    }

    public function test_school_admin_can_view_attendance_for_own_school_buses(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH100');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-100');

        $this->actingAs($admin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('BUS-100');

        $this->actingAs($admin)->get(route('attendance.buses.show', $bus))
            ->assertOk()
            ->assertSee('BUS-100');
    }

    public function test_school_admin_cannot_access_another_schools_bus(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH101');
        $admin->school_id = $school->id;
        $admin->save();

        $otherSchool = $this->createSchool('SCH102');
        $bus = $this->createBus($otherSchool, 'BUS-101');

        $this->actingAs($admin)->get(route('attendance.buses.show', $bus))
            ->assertForbidden();
    }

    public function test_super_admin_sees_all_buses(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = $this->createUser();
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('SCH103');
        $bus = $this->createBus($school, 'BUS-102');

        $this->actingAs($superAdmin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('BUS-102');

        $this->actingAs($superAdmin)->get(route('attendance.buses.show', $bus))
            ->assertOk();
    }

    public function test_driver_only_sees_their_own_bus(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH104');
        $driver = $this->createDriver($school, $driverUser, '104');

        $ownBus = $this->createBus($school, 'BUS-103', $driver);
        $otherBus = $this->createBus($school, 'BUS-104');

        $this->actingAs($driverUser)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('BUS-103')
            ->assertDontSee('BUS-104');

        $this->actingAs($driverUser)->get(route('attendance.buses.show', $otherBus))
            ->assertForbidden();
    }

    public function test_parent_cannot_access_attendance(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $parent = $this->createUser();
        $parent->assignRole('Parent');

        $this->actingAs($parent)->get(route('attendance.index'))
            ->assertForbidden();
    }

    public function test_school_admin_can_check_student_in(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH105');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-105');
        $student = $this->createStudent($school, $bus, 'ADM105');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect(route('attendance.buses.show', [
                'bus' => $bus,
                'date' => now()->toDateString(),
            ]));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'bus_id' => $bus->id,
            'trip' => 'home_to_school',
        ]);

        $attendance = Attendance::where('student_id', $student->id)
            ->where('trip', 'home_to_school')
            ->whereDate('date', now()->toDateString())
            ->first();
        $this->assertNotNull($attendance->check_in_at);
        $this->assertNull($attendance->check_out_at);
    }

    public function test_driver_can_mark_both_trips_in_a_single_day(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH105B');
        $driver = $this->createDriver($school, $driverUser, '105B');
        $bus = $this->createBus($school, 'BUS-105B', $driver);
        $student = $this->createStudent($school, $bus, 'ADM105B');

        foreach (['home_to_school', 'school_to_home'] as $trip) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                    'action' => 'check_in',
                    'trip' => $trip,
                ])
                ->assertRedirect();

            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                    'action' => 'check_out',
                    'trip' => $trip,
                ])
                ->assertRedirect();
        }

        $this->assertDatabaseCount('attendances', 2);

        $homeToSchool = Attendance::where('student_id', $student->id)
            ->where('trip', 'home_to_school')
            ->first();
        $schoolToHome = Attendance::where('student_id', $student->id)
            ->where('trip', 'school_to_home')
            ->first();

        $this->assertNotNull($homeToSchool->check_in_at);
        $this->assertNotNull($homeToSchool->check_out_at);
        $this->assertNotNull($schoolToHome->check_in_at);
        $this->assertNotNull($schoolToHome->check_out_at);
    }

    public function test_check_out_requires_a_prior_check_in(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH106');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-106');
        $student = $this->createStudent($school, $bus, 'ADM106');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertSessionHasErrors('trip');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_double_check_in_is_rejected(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH107');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-107');
        $student = $this->createStudent($school, $bus, 'ADM107');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertSessionHasErrors('trip');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_double_check_out_is_rejected(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH108');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-108');
        $student = $this->createStudent($school, $bus, 'ADM108');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertSessionHasErrors('trip');
    }

    public function test_student_from_another_bus_is_rejected(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH109');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-109');
        $otherBus = $this->createBus($school, 'BUS-110');
        $student = $this->createStudent($school, $otherBus, 'ADM109');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_index_lists_all_buses_regardless_of_status(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = $this->createUser(['name' => 'Super Admin User']);
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('SCH110');
        $activeBus = $this->createBus($school, 'BUS-111');
        $maintenanceBus = Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'BUS-112',
            'registration_number' => 'BA BUS-112',
            'capacity' => 40,
            'status' => 'Maintenance',
        ]);

        $this->actingAs($superAdmin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('BUS-111')
            ->assertSee('BUS-112')
            ->assertSee('Maintenance');
    }

    public function test_attendance_is_blocked_for_non_active_buses(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH111');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'BUS-113',
            'registration_number' => 'BA BUS-113',
            'capacity' => 40,
            'status' => 'Inactive',
        ]);
        $student = $this->createStudent($school, $bus, 'ADM110');

        $this->actingAs($admin)->get(route('attendance.buses.show', $bus))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_school_admin_can_view_bus_attendance_history(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser(['name' => 'Admin Who Marked']);
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH112');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-114');
        $student = $this->createStudent($school, $bus, 'ADM111');

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $bus->id,
            'date' => now()->toDateString(),
            'check_in_at' => now(),
            'marked_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('attendance.buses.history', $bus))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('ADM111')
            ->assertSee('Admin Who Marked');
    }

    public function test_school_admin_cannot_view_another_schools_bus_history(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH113');
        $admin->school_id = $school->id;
        $admin->save();

        $otherSchool = $this->createSchool('SCH114');
        $bus = $this->createBus($otherSchool, 'BUS-115');

        $this->actingAs($admin)->get(route('attendance.buses.history', $bus))
            ->assertForbidden();
    }

    public function test_driver_can_view_own_bus_history_but_not_others(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH115');
        $driver = $this->createDriver($school, $driverUser, '115');

        $ownBus = $this->createBus($school, 'BUS-116', $driver);
        $otherBus = $this->createBus($school, 'BUS-117');

        $this->actingAs($driverUser)->get(route('attendance.buses.history', $ownBus))
            ->assertOk();

        $this->actingAs($driverUser)->get(route('attendance.buses.history', $otherBus))
            ->assertForbidden();
    }

    public function test_history_respects_date_range_filter(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH116');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-118');

        $studentA = $this->createStudent($school, $bus, 'ADM112');
        $studentB = $this->createStudent($school, $bus, 'ADM113');

        Attendance::create([
            'student_id' => $studentA->id,
            'bus_id' => $bus->id,
            'date' => now()->toDateString(),
            'check_in_at' => now(),
            'marked_by' => $admin->id,
        ]);

        Attendance::create([
            'student_id' => $studentB->id,
            'bus_id' => $bus->id,
            'date' => now()->subDays(40)->toDateString(),
            'check_in_at' => now()->subDays(40),
            'marked_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('attendance.buses.history', [
            'bus' => $bus,
            'from' => now()->subDays(10)->toDateString(),
            'to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('ADM112')
            ->assertDontSee('ADM113');
    }

    public function test_school_to_home_requires_home_to_school_completion(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH118');
        $driver = $this->createDriver($school, $driverUser, '118');
        $bus = $this->createBus($school, 'BUS-120', $driver);
        $student = $this->createStudent($school, $bus, 'ADM114');

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'school_to_home',
            ])
            ->assertSessionHasErrors('trip');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_school_to_home_allowed_once_home_to_school_completed(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH119');
        $driver = $this->createDriver($school, $driverUser, '119');
        $bus = $this->createBus($school, 'BUS-121', $driver);
        $student = $this->createStudent($school, $bus, 'ADM115');

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'school_to_home',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('attendances', 2);
    }

    public function test_strict_sequence_is_enforced_at_each_stage(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH119B');
        $driver = $this->createDriver($school, $driverUser, '119B');
        $bus = $this->createBus($school, 'BUS-121B', $driver);
        $student = $this->createStudent($school, $bus, 'ADM115B');

        $mark = function (string $action, string $trip) use ($bus, $student, $driverUser) {
            return $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                    'action' => $action,
                    'trip' => $trip,
                ]);
        };

        $mark('check_out', 'home_to_school')->assertSessionHasErrors('trip');
        $mark('check_in', 'school_to_home')->assertSessionHasErrors('trip');
        $mark('check_out', 'school_to_home')->assertSessionHasErrors('trip');
        $this->assertDatabaseCount('attendances', 0);

        $mark('check_in', 'home_to_school')->assertRedirect();
        $mark('check_in', 'school_to_home')->assertSessionHasErrors('trip');
        $mark('check_out', 'school_to_home')->assertSessionHasErrors('trip');
        $this->assertDatabaseCount('attendances', 1);

        $mark('check_out', 'home_to_school')->assertRedirect();
        $mark('check_out', 'school_to_home')->assertSessionHasErrors('trip');
        $this->assertDatabaseCount('attendances', 1);

        $mark('check_in', 'school_to_home')->assertRedirect();
        $mark('check_out', 'home_to_school')->assertSessionHasErrors('trip');
        $this->assertDatabaseCount('attendances', 2);

        $mark('check_out', 'school_to_home')->assertRedirect();
        $this->assertDatabaseCount('attendances', 2);

        $mark('check_in', 'home_to_school')->assertSessionHasErrors('trip');

        $this->assertNotNull(Attendance::where('student_id', $student->id)->where('trip', 'home_to_school')->first()->check_in_at);
        $this->assertNotNull(Attendance::where('student_id', $student->id)->where('trip', 'home_to_school')->first()->check_out_at);
        $this->assertNotNull(Attendance::where('student_id', $student->id)->where('trip', 'school_to_home')->first()->check_in_at);
        $this->assertNotNull(Attendance::where('student_id', $student->id)->where('trip', 'school_to_home')->first()->check_out_at);
    }

    public function test_attendance_index_shows_completed_state_when_day_completed(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH120');
        $driver = $this->createDriver($school, $driverUser, '120');
        $bus = $this->createBus($school, 'BUS-122', $driver);
        $student = $this->createStudent($school, $bus, 'ADM116');

        foreach ([
            ['action' => 'check_in', 'trip' => 'home_to_school'],
            ['action' => 'check_out', 'trip' => 'home_to_school'],
            ['action' => 'check_in', 'trip' => 'school_to_home'],
            ['action' => 'check_out', 'trip' => 'school_to_home'],
        ] as $markup) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), $markup)
                ->assertRedirect();
        }

        $response = $this->actingAs($driverUser)->get(route('attendance.index'));
        $response->assertOk();
        $response->assertSee('Attendance Completed');
        $response->assertDontSee('Add Next Day Attendance');
        $response->assertDontSee("Add Today's Attendance", false);
    }

    public function test_attendance_index_shows_today_button_until_day_completed(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH121');
        $driver = $this->createDriver($school, $driverUser, '121');
        $bus = $this->createBus($school, 'BUS-123', $driver);
        $student = $this->createStudent($school, $bus, 'ADM117');

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $response = $this->actingAs($driverUser)->get(route('attendance.index'));
        $response->assertOk();
        $response->assertSee("Add Today's Attendance", false);
        $response->assertDontSee('Add Next Day Attendance');
    }

    public function test_show_page_renders_student_stage_progress(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH122');
        $driver = $this->createDriver($school, $driverUser, '122');
        $bus = $this->createBus($school, 'BUS-124', $driver);
        $student = $this->createStudent($school, $bus, 'ADM118');

        $response = $this->actingAs($driverUser)->get(route('attendance.buses.show', $bus));
        $response->assertOk();
        $response->assertSee('Picked Up from Home');
        $response->assertSee('Dropped at School');
        $response->assertSee('Picked Up from School');
        $response->assertSee('Dropped at Home');
        $response->assertSee('Pick Up');

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $response = $this->actingAs($driverUser)->get(route('attendance.buses.show', $bus));
        $response->assertOk();
        $response->assertSee('Drop at School');
        $response->assertDontSee('name="action" value="check_in"');
    }

    public function test_attendance_cannot_be_marked_for_a_non_today_date(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH123');
        $driver = $this->createDriver($school, $driverUser, '123');
        $bus = $this->createBus($school, 'BUS-125', $driver);
        $student = $this->createStudent($school, $bus, 'ADM119');

        $tomorrow = now()->addDay()->toDateString();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
                'date' => $tomorrow,
            ])
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_show_page_blocks_attendance_with_alert_when_todays_attendance_taken(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH124');
        $driver = $this->createDriver($school, $driverUser, '124');
        $bus = $this->createBus($school, 'BUS-126', $driver);
        $student = $this->createStudent($school, $bus, 'ADM120');

        foreach ([
            ['action' => 'check_in', 'trip' => 'home_to_school'],
            ['action' => 'check_out', 'trip' => 'home_to_school'],
            ['action' => 'check_in', 'trip' => 'school_to_home'],
            ['action' => 'check_out', 'trip' => 'school_to_home'],
        ] as $markup) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['bus' => $bus, 'student' => $student]), $markup)
                ->assertRedirect();
        }

        $response = $this->actingAs($driverUser)->get(route('attendance.buses.show', $bus));
        $response->assertOk();
        $response->assertSee("Today's attendance has already been taken. Please try again tomorrow.", false);
        $response->assertDontSee('name="action" value="check_in"');
        $response->assertDontSee('name="action" value="check_out"');
    }

    public function test_show_page_is_read_only_when_viewing_a_past_date(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH125');
        $driver = $this->createDriver($school, $driverUser, '125');
        $bus = $this->createBus($school, 'BUS-127', $driver);
        $this->createStudent($school, $bus, 'ADM121');

        $yesterday = now()->subDay()->toDateString();

        $response = $this->actingAs($driverUser)->get(route('attendance.buses.show', [
            'bus' => $bus,
            'date' => $yesterday,
        ]));
        $response->assertOk();
        $response->assertSee('read-only view');
        $response->assertDontSee('name="action" value="check_in"');
        $response->assertDontSee('name="action" value="check_out"');
    }

    public function test_history_shows_empty_state(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH117');
        $admin->school_id = $school->id;
        $admin->save();

        $bus = $this->createBus($school, 'BUS-119');

        $this->actingAs($admin)->get(route('attendance.buses.history', $bus))
            ->assertOk()
            ->assertSee('No attendance records found for this period.');
    }
}
