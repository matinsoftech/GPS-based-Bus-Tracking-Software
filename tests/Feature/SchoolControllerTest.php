<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolControllerTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH001',
            'email' => 'admin@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);
    }

    private function makeStudent(string $admissionNo, string $firstName, bool $active = true): Student
    {
        $parentUser = User::factory()->create();
        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Parent '.$firstName,
            'phone' => '9800000'.random_int(100, 999),
            'address' => 'Kathmandu',
        ]);

        return Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'admission_no' => $admissionNo,
            'first_name' => $firstName,
            'last_name' => 'Sharma',
            'date_of_birth' => '2015-01-01',
            'gender' => 'Male',
            'grade' => '1',
            'section' => 'A',
            'pickup_location' => 'Location A',
            'drop_location' => 'Location B',
            'is_active' => $active,
        ]);
    }

    public function test_school_show_page_displays_aggregate_counts(): void
    {
        $this->makeStudent('ADM-001', 'Ram', true);
        $this->makeStudent('ADM-002', 'Sita', true);

        Driver::create([
            'school_id' => $this->school->id,
            'employee_id' => 'DR001',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'email' => 'ramesh@example.com',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-001',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->user->id,
        ]);

        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-002',
            'registration_number' => 'BA 1 KHA 1235',
            'capacity' => 40,
            'status' => 'Maintenance',
        ]);

        $route = Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route A',
            'route_code' => 'R-001',
            'start_location' => 'Start A',
            'end_location' => 'End A',
            'is_active' => true,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'name' => 'Stop 1',
            'latitude' => 27.7172,
            'longitude' => 85.324,
            'stop_order' => 1,
        ]);
        RouteStop::create([
            'route_id' => $route->id,
            'name' => 'Stop 2',
            'latitude' => 27.7173,
            'longitude' => 85.3241,
            'stop_order' => 2,
        ]);

        $parentUser = User::factory()->create();
        ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
        ]);

        SchoolAdmin::create([
            'school_id' => $this->school->id,
            'user_id' => $this->user->id,
            'name' => 'Admin',
            'phone' => '9800000003',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->actingAs($this->user)
            ->get(route('schools.show', $this->school))
            ->assertOk()
            ->assertSee('Bright Future School')
            ->assertSee('Students')
            ->assertSee('Drivers')
            ->assertSee('Buses')
            ->assertSee('Routes')
            ->assertSee('Route Stops')
            ->assertSee('Parents')
            ->assertSee('School Admins')
            ->assertSee('2 active')
            ->assertSee('1 active · 1 maintenance · 0 inactive');
    }
}
