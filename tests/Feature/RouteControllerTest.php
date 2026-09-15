<?php

namespace Tests\Feature;

use App\Models\Route;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createSchool(string $name, string $code): School
    {
        return School::create([
            'name' => $name,
            'code' => $code,
            'email' => 'admin@'.$code.'.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_update_route(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolId = $school->id;

        $route = Route::create([
            'school_id' => $schoolId,
            'name' => 'Route A',
            'route_code' => 'RA',
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $response = $this->actingAs($superAdmin)->put(route('routes.update', $route), [
            'name' => 'Route A Updated',
            'route_code' => 'RA',
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'estimated_distance' => 5.5,
            'estimated_duration' => 30,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('routes.index'));

        $route->refresh();
        $this->assertSame('Route A Updated', $route->name);
        $this->assertSame($schoolId, $route->school_id);
    }

    public function test_super_admin_cannot_change_route_school_through_edit(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');
        $otherSchool = $this->createSchool('Moonlight School', 'SCH-B');

        $route = Route::create([
            'school_id' => $school->id,
            'name' => 'Route A',
            'route_code' => 'RA',
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $response = $this->actingAs($superAdmin)->put(route('routes.update', $route), [
            'name' => 'Route A',
            'route_code' => 'RA',
            'route_type' => Route::ROUTE_TYPE_HOME_TO_SCHOOL,
            'start_location' => 'Kathmandu',
            'end_location' => 'School',
            'school_id' => $otherSchool->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('routes.index'));

        $route->refresh();
        $this->assertSame($school->id, $route->school_id);
    }
}