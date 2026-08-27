<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
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

    private function createRoute(School $school, string $routeName, bool $isActive = true): Route
    {
        return Route::create([
            'name' => $routeName,
            'route_code' => 'RT-'.strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $routeName), 0, 8)).'-'.bin2hex(random_bytes(3)),
            'school_id' => $school->id,
            'is_active' => $isActive,
            'start_location' => 'Start Point',
            'end_location' => 'End Point',
        ]);
    }

    private function createStudent(School $school, Route $route, string $admissionNo, ?ParentProfile $parent = null): Student
    {
        $parent ??= $this->createParent($school);

        return Student::create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'route_id' => $route->id,
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

    public function test_school_admin_can_view_attendance_for_own_school_routes(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH100');
        $admin->school_id = $school->id;
        $admin->save();

        $route = $this->createRoute($school, 'Route 100');

        $this->actingAs($admin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Route 100');

        $this->actingAs($admin)->get(route('attendance.routes.show', $route))
            ->assertOk()
            ->assertSee('Route 100');
    }

    public function test_school_admin_cannot_access_another_schools_route(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH101');
        $admin->school_id = $school->id;
        $admin->save();

        $otherSchool = $this->createSchool('SCH102');
        $route = $this->createRoute($otherSchool, 'Route 101');

        $this->actingAs($admin)->get(route('attendance.routes.show', $route))
            ->assertForbidden();
    }

    public function test_super_admin_sees_all_routes(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = $this->createUser();
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('SCH103');
        $route = $this->createRoute($school, 'Route 102');

        $this->actingAs($superAdmin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Route 102');

        $this->actingAs($superAdmin)->get(route('attendance.routes.show', $route))
            ->assertOk();
    }

    public function test_driver_only_sees_their_own_route(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH104');
        $driver = $this->createDriver($school, $driverUser, '104');

        $ownRoute = $this->createRoute($school, 'Route 103');
        $otherRoute = $this->createRoute($school, 'Route 104');

        $ownRoute->drivers()->attach($driver->id);

        $this->actingAs($driverUser)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Route 103')
            ->assertDontSee('Route 104');

        $this->actingAs($driverUser)->get(route('attendance.routes.show', $otherRoute))
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

        $route = $this->createRoute($school, 'Route 105');
        $student = $this->createStudent($school, $route, 'ADM105');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect(route('attendance.routes.show', [
                'route' => $route,
                'date' => now()->toDateString(),
            ]));

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'route_id' => $route->id,
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
        $route = $this->createRoute($school, 'Route 105B');
        $student = $this->createStudent($school, $route, 'ADM105B');

        $route->drivers()->attach($driver->id);

        foreach (['home_to_school', 'school_to_home'] as $trip) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                    'action' => 'check_in',
                    'trip' => $trip,
                ])
                ->assertRedirect();

            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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

        $route = $this->createRoute($school, 'Route 106');
        $student = $this->createStudent($school, $route, 'ADM106');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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

        $route = $this->createRoute($school, 'Route 107');
        $student = $this->createStudent($school, $route, 'ADM107');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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

        $route = $this->createRoute($school, 'Route 108');
        $student = $this->createStudent($school, $route, 'ADM108');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertSessionHasErrors('trip');
    }

    public function test_student_from_another_route_is_rejected(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH109');
        $admin->school_id = $school->id;
        $admin->save();

        $route = $this->createRoute($school, 'Route 109');
        $otherRoute = $this->createRoute($school, 'Route 110');
        $student = $this->createStudent($school, $otherRoute, 'ADM109');

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_index_lists_all_routes_regardless_of_status(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = $this->createUser(['name' => 'Super Admin User']);
        $superAdmin->assignRole('Super Admin');

        $school = $this->createSchool('SCH110');
        $activeRoute = $this->createRoute($school, 'Route 111');
        $inactiveRoute = Route::create([
            'name' => 'Route 112',
            'route_code' => 'RT-112',
            'school_id' => $school->id,
            'is_active' => false,
            'start_location' => 'Start Point',
            'end_location' => 'End Point',
        ]);

        $this->actingAs($superAdmin)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Route 111')
            ->assertSee('Route 112');
    }

    public function test_attendance_is_blocked_for_inactive_routes(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH111');
        $admin->school_id = $school->id;
        $admin->save();

        $route = $this->createRoute($school, 'Route 113', false);
        $student = $this->createStudent($school, $route, 'ADM110');

        $this->actingAs($admin)->get(route('attendance.routes.show', $route))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_school_admin_can_view_route_attendance_history(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser(['name' => 'Admin Who Marked']);
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH112');
        $admin->school_id = $school->id;
        $admin->save();

        $route = $this->createRoute($school, 'Route 114');
        $student = $this->createStudent($school, $route, 'ADM111');

        Attendance::create([
            'student_id' => $student->id,
            'route_id' => $route->id,
            'date' => now()->toDateString(),
            'check_in_at' => now(),
            'marked_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('attendance.routes.history', $route))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('ADM111')
            ->assertSee('Admin Who Marked');
    }

    public function test_school_admin_cannot_view_another_schools_route_history(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $admin = $this->createUser();
        $admin->assignRole('School Admin');

        $school = $this->createSchool('SCH113');
        $admin->school_id = $school->id;
        $admin->save();

        $otherSchool = $this->createSchool('SCH114');
        $route = $this->createRoute($otherSchool, 'Route 115');

        $this->actingAs($admin)->get(route('attendance.routes.history', $route))
            ->assertForbidden();
    }

    public function test_driver_can_view_own_route_history_but_not_others(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = $this->createUser();
        $driverUser->assignRole('Driver');

        $school = $this->createSchool('SCH115');
        $driver = $this->createDriver($school, $driverUser, '115');

        $ownRoute = $this->createRoute($school, 'Route 116');
        $otherRoute = $this->createRoute($school, 'Route 117');

        $ownRoute->drivers()->attach($driver->id);

        $this->actingAs($driverUser)->get(route('attendance.routes.history', $ownRoute))
            ->assertOk();

        $this->actingAs($driverUser)->get(route('attendance.routes.history', $otherRoute))
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

        $route = $this->createRoute($school, 'Route 118');

        $studentA = $this->createStudent($school, $route, 'ADM112');
        $studentB = $this->createStudent($school, $route, 'ADM113');

        Attendance::create([
            'student_id' => $studentA->id,
            'route_id' => $route->id,
            'date' => now()->toDateString(),
            'check_in_at' => now(),
            'marked_by' => $admin->id,
        ]);

        Attendance::create([
            'student_id' => $studentB->id,
            'route_id' => $route->id,
            'date' => now()->subDays(40)->toDateString(),
            'check_in_at' => now()->subDays(40),
            'marked_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('attendance.routes.history', [
            'route' => $route,
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
        $route = $this->createRoute($school, 'Route 120');
        $student = $this->createStudent($school, $route, 'ADM114');

        $route->drivers()->attach($driver->id);

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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
        $route = $this->createRoute($school, 'Route 121');
        $student = $this->createStudent($school, $route, 'ADM115');

        $route->drivers()->attach($driver->id);

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_out',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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
        $route = $this->createRoute($school, 'Route 121B');
        $student = $this->createStudent($school, $route, 'ADM115B');

        $route->drivers()->attach($driver->id);

        $mark = function (string $action, string $trip) use ($route, $student, $driverUser) {
            return $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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
        $route = $this->createRoute($school, 'Route 122');
        $student = $this->createStudent($school, $route, 'ADM116');

        $route->drivers()->attach($driver->id);

        foreach ([
            ['action' => 'check_in', 'trip' => 'home_to_school'],
            ['action' => 'check_out', 'trip' => 'home_to_school'],
            ['action' => 'check_in', 'trip' => 'school_to_home'],
            ['action' => 'check_out', 'trip' => 'school_to_home'],
        ] as $markup) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), $markup)
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
        $route = $this->createRoute($school, 'Route 123');
        $student = $this->createStudent($school, $route, 'ADM117');

        $route->drivers()->attach($driver->id);

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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
        $route = $this->createRoute($school, 'Route 124');
        $student = $this->createStudent($school, $route, 'ADM118');

        $route->drivers()->attach($driver->id);

        $response = $this->actingAs($driverUser)->get(route('attendance.routes.show', $route));
        $response->assertOk();
        $response->assertSee('Picked Up from Home');
        $response->assertSee('Dropped at School');
        $response->assertSee('Picked Up from School');
        $response->assertSee('Dropped at Home');
        $response->assertSee('Pick Up');

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
                'action' => 'check_in',
                'trip' => 'home_to_school',
            ])
            ->assertRedirect();

        $response = $this->actingAs($driverUser)->get(route('attendance.routes.show', $route));
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
        $route = $this->createRoute($school, 'Route 125');
        $student = $this->createStudent($school, $route, 'ADM119');

        $route->drivers()->attach($driver->id);

        $tomorrow = now()->addDay()->toDateString();

        $this->actingAs($driverUser)
            ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), [
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
        $route = $this->createRoute($school, 'Route 126');
        $student = $this->createStudent($school, $route, 'ADM120');

        $route->drivers()->attach($driver->id);

        foreach ([
            ['action' => 'check_in', 'trip' => 'home_to_school'],
            ['action' => 'check_out', 'trip' => 'home_to_school'],
            ['action' => 'check_in', 'trip' => 'school_to_home'],
            ['action' => 'check_out', 'trip' => 'school_to_home'],
        ] as $markup) {
            $this->actingAs($driverUser)
                ->post(route('attendance.mark', ['route' => $route, 'student' => $student]), $markup)
                ->assertRedirect();
        }

        $response = $this->actingAs($driverUser)->get(route('attendance.routes.show', $route));
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
        $route = $this->createRoute($school, 'Route 127');
        $this->createStudent($school, $route, 'ADM121');

        $route->drivers()->attach($driver->id);

        $yesterday = now()->subDay()->toDateString();

        $response = $this->actingAs($driverUser)->get(route('attendance.routes.show', [
            'route' => $route,
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

        $route = $this->createRoute($school, 'Route 119');

        $this->actingAs($admin)->get(route('attendance.routes.history', $route))
            ->assertOk()
            ->assertSee('No attendance records found for this period.');
    }
}
