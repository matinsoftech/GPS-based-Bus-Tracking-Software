<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_open_dedicated_live_tracking_page(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driverUser = User::factory()->create();
        $driverUser->assignRole('Driver');

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-LT1',
            'email' => 'lt@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'school_id' => $school->id,
            'user_id' => $driverUser->id,
            'employee_id' => 'DR-LT1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-LT1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driverUser->id,
        ]);

        $bus = Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'LT-BUS-1',
            'registration_number' => 'BA LT-BUS-1',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $bus->drivers()->attach($driver->id);

        $response = $this->actingAs($driverUser)->get(route('driver.live-tracking'));
        $response->assertOk();
        $response->assertSee('Live Tracking');
        $response->assertSee('LT-BUS-1');
    }

    public function test_driver_live_tracking_page_requires_driver_role(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $parentUser = User::factory()->create();
        $parentUser->assignRole('Parent');

        $this->actingAs($parentUser)->get(route('driver.live-tracking'))->assertForbidden();
    }

    public function test_super_admin_can_create_driver(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH001',
            'email' => 'admin@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('drivers.create'));
        $response->assertOk();

        $response = $this->actingAs($user)->post(route('drivers.store'), [
            'employee_id' => 'DR001',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'email' => 'ramesh@example.com',
            'password' => 'password123',
            'address' => 'Kathmandu',
            'city' => 'Kathmandu',
            'state' => 'Bagmati',
            'country' => 'Nepal',
            'postal_code' => '44600',
            'license_number' => 'LIC-001',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'experience_years' => 5,
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'school_id' => $school->id,
            'emergency_contact_name' => 'Sita Sharma',
            'emergency_contact_phone' => '9800000002',
            'remarks' => 'Reliable driver',
        ]);

        $response->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', [
            'employee_id' => 'DR001',
            'school_id' => $school->id,
        ]);

        $driverUser = User::where('email', 'ramesh@example.com')->first();
        $this->assertNotNull($driverUser);
        $this->assertTrue($driverUser->hasRole('Driver'));
        $this->assertDatabaseHas('drivers', [
            'employee_id' => 'DR001',
            'user_id' => $driverUser->id,
        ]);
    }

    public function test_store_requires_email_and_password(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH002',
            'email' => 'admin@brightfuture2.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('drivers.store'), [
            'employee_id' => 'DR002',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'email' => '',
            'password' => '',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-002',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'school_id' => $school->id,
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertDatabaseMissing('drivers', ['employee_id' => 'DR002']);
        $this->assertDatabaseMissing('users', ['email' => '']);
    }
}
