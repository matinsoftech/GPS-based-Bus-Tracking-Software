<?php

namespace Tests\Feature;

use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_create_student_for_their_own_school(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH001',
            'email' => 'admin@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'school_id' => $school->id,
        ]);
        $admin->assignRole('School Admin');

        $parentUser = User::factory()->create();
        $parentUser->assignRole('Parent');

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $school->id,
            'name' => 'Ramesh Shrestha',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
        ]);

        $route = Route::create([
            'name' => 'Route 1',
            'route_code' => 'RT-S1-001',
            'school_id' => $school->id,
            'start_location' => 'Start',
            'end_location' => 'End',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('students.create'));
        $response->assertOk();

        $response = $this->actingAs($admin)->post(route('students.store'), [
            'admission_no' => 'STD001',
            'parent_id' => $parent->id,
            'first_name' => 'Anita',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2012-01-01',
            'gender' => 'Female',
            'grade' => '7',
            'section' => 'A',
            'roll_no' => '01',
            'pickup_location' => 'Gaushala',
            'drop_location' => 'Bright Future School',
            'route_id' => $route->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'admission_no' => 'STD001',
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'route_id' => $route->id,
            'first_name' => 'Anita',
        ]);
    }

    public function test_bus_from_another_school_is_rejected(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH001',
            'email' => 'admin@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $otherSchool = School::create([
            'name' => 'Other School',
            'code' => 'SCH002',
            'email' => 'admin@other.com',
            'phone' => '9800000001',
            'address' => 'Lalitpur',
            'principal_name' => 'Principal Other',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'school_id' => $school->id,
        ]);
        $admin->assignRole('School Admin');

        $parentUser = User::factory()->create();
        $parentUser->assignRole('Parent');

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $school->id,
            'name' => 'Ramesh Shrestha',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
        ]);

        $otherRoute = Route::create([
            'name' => 'Route 2',
            'route_code' => 'RT-S2-001',
            'school_id' => $otherSchool->id,
            'start_location' => 'Start',
            'end_location' => 'End',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('students.store'), [
            'admission_no' => 'STD002',
            'parent_id' => $parent->id,
            'first_name' => 'Bikash',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2013-01-01',
            'gender' => 'Male',
            'grade' => '6',
            'section' => 'B',
            'pickup_location' => 'Koteshwor',
            'drop_location' => 'Bright Future School',
            'route_id' => $otherRoute->id,
        ]);

        $response->assertSessionHasErrors('route_id');

        $this->assertDatabaseMissing('students', [
            'admission_no' => 'STD002',
        ]);
    }
}
