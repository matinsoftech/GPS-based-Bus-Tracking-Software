<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_bus_with_driver_and_route(): void
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

        $route = Route::create([
            'school_id' => $school->id,
            'name' => 'Baneshwor Shuttle',
            'route_code' => 'R1',
            'start_location' => 'Baneshwor',
            'end_location' => 'School',
        ]);

        $driver = Driver::create([
            'school_id' => $school->id,
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
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('buses.create'));
        $response->assertOk();

        $response = $this->actingAs($user)->post(route('buses.store'), [
            'school_id' => $school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'model' => 'Viking',
            'year' => 2022,
            'capacity' => 40,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1001',
            'insurance_number' => 'INS-001',
            'insurance_expiry_date' => '2027-01-01',
            'last_service_date' => '2026-01-01',
            'status' => 'Active',
            'notes' => 'Brand new bus',
            'route_ids' => [$route->id],
            'driver_id' => $driver->id,
        ]);

        $response->assertRedirect(route('buses.index'));

        $this->assertDatabaseHas('buses', [
            'bus_number' => 'BUS-001',
            'school_id' => $school->id,
            'driver_id' => $driver->id,
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('bus_route', [
            'bus_id' => Bus::where('bus_number', 'BUS-001')->first()->id,
            'route_id' => $route->id,
        ]);

        $bus = Bus::where('bus_number', 'BUS-001')->first();
        $this->assertNotNull($bus);
        $this->assertTrue($bus->routes->contains($route));
        $this->assertTrue($bus->driver->is($driver));
    }

    public function test_duplicate_bus_number_is_rejected(): void
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

        Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('buses.store'), [
            'school_id' => $school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 9999',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors('bus_number');

        $this->assertSame(1, Bus::count());
    }

    public function test_school_admin_can_edit_their_own_schools_bus(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $schoolAdmin = User::factory()->create(['school_id' => null]);
        $schoolAdmin->assignRole('School Admin');

        $school = School::create([
            'name' => 'Green Valley High School',
            'code' => 'SCH003',
            'email' => 'admin@greenvalley.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal Name',
            'status' => 'active',
        ]);

        $schoolAdmin->school_id = $school->id;
        $schoolAdmin->save();

        $route = Route::create([
            'school_id' => $school->id,
            'name' => 'Green Valley Shuttle',
            'route_code' => 'GV-R1',
            'start_location' => 'Baneshwor',
            'end_location' => 'School',
        ]);

        $driver = Driver::create([
            'school_id' => $school->id,
            'employee_id' => 'DR010',
            'first_name' => 'Hari',
            'last_name' => 'Tamang',
            'gender' => 'Male',
            'date_of_birth' => '1988-05-10',
            'phone' => '9800000100',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-010',
            'license_type' => 'Bus',
            'license_issue_date' => '2019-01-01',
            'license_expiry_date' => '2029-01-01',
            'joining_date' => '2023-01-01',
            'status' => 'Active',
            'created_by' => $schoolAdmin->id,
        ]);

        $bus = Bus::create([
            'school_id' => $school->id,
            'bus_number' => 'BUS-010',
            'registration_number' => 'BA 1 KHA 1111',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($schoolAdmin)->get(route('buses.edit', $bus));
        $response->assertOk();

        $response = $this->actingAs($schoolAdmin)->put(route('buses.update', $bus), [
            'bus_number' => 'BUS-010',
            'registration_number' => 'BA 1 KHA 1111',
            'capacity' => 50,
            'status' => 'Maintenance',
            'route_ids' => [$route->id],
            'driver_id' => $driver->id,
        ]);

        $response->assertRedirect(route('buses.index'));

        $bus->refresh();

        $this->assertSame(50, $bus->capacity);
        $this->assertSame('Maintenance', $bus->status);
        $this->assertTrue($bus->routes->contains($route));
        $this->assertSame($driver->id, $bus->driver_id);
    }
}
