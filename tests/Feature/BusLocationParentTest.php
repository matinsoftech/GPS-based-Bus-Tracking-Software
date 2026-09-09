<?php

namespace Tests\Feature;

use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusLocationParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_select_between_multiple_assigned_routes(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $parent = $this->createParent('Parent A', 'parenta@example.com', $school);
        $child = $this->createStudent($parent, 'Alice Child');

        $routeA = $this->createRoute($school, 'Route A', 'RA', Route::ROUTE_TYPE_HOME_TO_SCHOOL);
        $routeB = $this->createRoute($school, 'Route B', 'RB', Route::ROUTE_TYPE_SCHOOL_TO_HOME);

        $this->createStop($routeA, 'Stop A1 - Riverside', 1, 27.7000, 85.3000);
        $this->createStop($routeA, 'Stop A2 - School Gate', 2, 27.7100, 85.3100);

        $this->createStop($routeB, 'Stop B1 - Downtown', 1, 27.7200, 85.2900);
        $this->createStop($routeB, 'Stop B2 - School Gate', 2, 27.7100, 85.3100);

        $child->routes()->attach([$routeA->id, $routeB->id]);

        $response = $this->actingAs($parent->user)
            ->get(route('bus_location', ['child_id' => $child->id, 'route_id' => $routeB->id]));

        $response->assertOk();
        $response->assertSee('Route B');
        $response->assertSee('Code: RB');
        $response->assertDontSee('Code: RA');
        $response->assertSee('Stop B1 - Downtown');
        $response->assertDontSee('Stop A1 - Riverside');
        $response->assertSee('School to Home');
        $response->assertSee('bg-brand-500 text-white border-brand-500 shadow-sm');
    }

    public function test_route_id_not_assigned_to_child_falls_back_to_default(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $parent = $this->createParent('Parent A', 'parenta@example.com', $school);
        $child = $this->createStudent($parent, 'Alice Child');

        $routeA = $this->createRoute($school, 'Route A', 'RA', Route::ROUTE_TYPE_HOME_TO_SCHOOL);
        $routeC = $this->createRoute($school, 'Route C', 'RC', Route::ROUTE_TYPE_SCHOOL_TO_HOME);

        $this->createStop($routeA, 'Stop A1 - Riverside', 1, 27.7000, 85.3000);
        $this->createStop($routeA, 'Stop A2 - School Gate', 2, 27.7100, 85.3100);
        $this->createStop($routeC, 'Stop C1 - Mall', 1, 27.7300, 85.3300);

        $child->routes()->attach([$routeA->id]);

        $response = $this->actingAs($parent->user)
            ->get(route('bus_location', ['child_id' => $child->id, 'route_id' => $routeC->id]));

        $response->assertOk();
        $response->assertSee('Code: RA');
        $response->assertDontSee('Code: RC');
        $response->assertSee('Stop A1 - Riverside');
        $response->assertDontSee('Stop C1 - Mall');

        $response = $this->actingAs($parent->user)
            ->get(route('bus_location', ['child_id' => $child->id, 'route_id' => 999999]));

        $response->assertOk();
        $response->assertSee('Code: RA');
    }

    public function test_latest_json_scopes_payload_to_selected_route(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $parent = $this->createParent('Parent A', 'parenta@example.com', $school);
        $child = $this->createStudent($parent, 'Alice Child');

        $routeA = $this->createRoute($school, 'Route A', 'RA', Route::ROUTE_TYPE_HOME_TO_SCHOOL);
        $routeB = $this->createRoute($school, 'Route B', 'RB', Route::ROUTE_TYPE_SCHOOL_TO_HOME);

        $this->createStop($routeA, 'Stop A1 - Riverside', 1, 27.7000, 85.3000);
        $this->createStop($routeA, 'Stop A2 - School Gate', 2, 27.7100, 85.3100);
        $this->createStop($routeB, 'Stop B1 - Downtown', 1, 27.7200, 85.2900);
        $this->createStop($routeB, 'Stop B2 - School Gate', 2, 27.7100, 85.3100);

        $child->routes()->attach([$routeA->id, $routeB->id]);

        $response = $this->actingAs($parent->user)
            ->getJson(route('bus_location.latest', ['child_id' => $child->id, 'route_id' => $routeB->id, 'view' => 'fleet']));

        $response->assertOk();
        $payload = $response->json();

        $this->assertSame([], $payload['buses']);
        $this->assertCount(1, $payload['routes']);
        $this->assertSame($routeB->id, $payload['routes'][0]['id']);
        $stopNames = collect($payload['routes'][0]['stops'])->pluck('name')->all();
        $this->assertContains('Stop B1 - Downtown', $stopNames);
        $this->assertNotContains('Stop A1 - Riverside', $stopNames);

        $response = $this->actingAs($parent->user)
            ->getJson(route('bus_location.latest', ['child_id' => $child->id, 'route_id' => $routeB->id]));

        $response->assertOk();
    }

    public function test_student_assigned_stop_is_highlighted_on_selected_route(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $parent = $this->createParent('Parent A', 'parenta@example.com', $school);
        $child = $this->createStudent($parent, 'Alice Child');

        $routeA = $this->createRoute($school, 'Route A', 'RA', Route::ROUTE_TYPE_HOME_TO_SCHOOL);
        $routeB = $this->createRoute($school, 'Route B', 'RB', Route::ROUTE_TYPE_SCHOOL_TO_HOME);

        $stopA = $this->createStop($routeA, 'Stop A1 - Riverside', 1, 27.7000, 85.3000);
        $stopB = $this->createStop($routeB, 'Stop B1 - Downtown', 1, 27.7200, 85.2900);
        $this->createStop($routeB, 'Stop B2 - School Gate', 2, 27.7100, 85.3100);

        $child->routes()->attach([$routeA->id, $routeB->id]);
        $child->stops()->attach([$stopA->id, $stopB->id]);

        $response = $this->actingAs($parent->user)
            ->get(route('bus_location', ['child_id' => $child->id, 'route_id' => $routeB->id]));

        $response->assertOk();
        $response->assertSee('Your Stop');
        $response->assertSee('Stop B1 - Downtown');
        $response->assertDontSee('Stop A1 - Riverside');
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

    private function createParent(string $name, string $email, School $school): ParentProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'school_id' => $school->id,
        ]);
        $user->assignRole('Parent');

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => $name,
            'phone' => '9800000000',
            'address' => 'Kathmandu',
        ]);
    }

    private function createStudent(ParentProfile $parent, string $name): Student
    {
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');

        return Student::create([
            'school_id' => $parent->school_id,
            'parent_id' => $parent->id,
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => '2015-05-10',
            'gender' => 'Male',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Kathmandu',
            'drop_location' => 'Kathmandu',
            'is_active' => true,
        ]);
    }

    private function createRoute(School $school, string $name, string $code, string $routeType): Route
    {
        return Route::create([
            'school_id' => $school->id,
            'name' => $name,
            'route_code' => $code,
            'route_type' => $routeType,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'is_active' => true,
        ]);
    }

    private function createStop(Route $route, string $name, int $order, float $latitude, float $longitude): RouteStop
    {
        return RouteStop::create([
            'route_id' => $route->id,
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'stop_order' => $order,
            'is_active' => true,
        ]);
    }
}