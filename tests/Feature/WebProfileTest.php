<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebProfileTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-WEB-PROFILE',
            'email' => 'web-profile@brightfuture.com',
            'phone' => '9800000100',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);
    }

    public function test_driver_profile_page_shows_role_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Ramesh Sharma',
            'school_id' => $this->school->id,
        ]);
        $user->assignRole('Driver');

        Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $user->id,
            'employee_id' => 'DR-WEB-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000101',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-WEB-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Ramesh Sharma')
            ->assertSee('Driver')
            ->assertSee('9800000101')
            ->assertSee('LIC-WEB-1')
            ->assertSee('Male');
    }

    public function test_school_admin_profile_page_shows_designation_and_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Principal Sharma',
            'school_id' => $this->school->id,
        ]);
        $user->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Sharma',
            'phone' => '9800000102',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Principal Sharma')
            ->assertSee('School Admin')
            ->assertSee('9800000102')
            ->assertSee('Principal');
    }

    public function test_driver_can_update_phone_through_profile_form(): void
    {
        $user = User::factory()->create(['name' => 'Ramesh Sharma']);
        $user->assignRole('Driver');
        $user->driver()->create([
            'school_id' => $this->school->id,
            'employee_id' => 'DR-WEB-2',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000101',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-WEB-2',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Ramesh Sharma',
            'email' => $user->email,
            'phone' => '9811111111',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertDatabaseHas('drivers', [
            'user_id' => $user->id,
            'phone' => '9811111111',
        ]);
    }

    public function test_school_admin_can_update_phone_through_profile_form(): void
    {
        $user = User::factory()->create(['name' => 'Principal Sharma']);
        $user->assignRole('School Admin');
        SchoolAdmin::create([
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Sharma',
            'phone' => '9800000102',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Principal Sharma',
            'email' => $user->email,
            'phone' => '9811111112',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertDatabaseHas('school_admins', [
            'user_id' => $user->id,
            'phone' => '9811111112',
        ]);
    }
}