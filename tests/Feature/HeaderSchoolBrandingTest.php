<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderSchoolBrandingTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Green Valley High School',
            'code' => 'SCH001',
            'email' => 'green@example.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
            'logo' => 'schools/test-logo.png',
        ]);
    }

    public function test_driver_dashboard_header_shows_their_school_name(): void
    {
        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR001',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-001',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $this->actingAs($driverUser)
            ->get(route('driver.dashboard'))
            ->assertOk()
            ->assertSee('Green Valley High School');
    }

    public function test_parent_dashboard_header_shows_their_school_name(): void
    {
        $parentUser = User::factory()->create();
        $parentUser->assignRole('Parent');

        ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
        ]);

        $this->actingAs($parentUser)
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Green Valley High School');
    }

    public function test_principal_dashboard_header_shows_their_school_name(): void
    {
        $principalUser = User::factory()->create();
        $principalUser->assignRole('School Admin');

        SchoolAdmin::create([
            'school_id' => $this->school->id,
            'user_id' => $principalUser->id,
            'name' => 'Principal',
            'phone' => '9800000003',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->actingAs($principalUser)
            ->get(route('principal.dashboard'))
            ->assertOk()
            ->assertSee('Green Valley High School');
    }
}
