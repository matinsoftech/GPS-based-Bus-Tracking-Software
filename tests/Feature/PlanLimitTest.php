<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Plan;
use App\Models\Route;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-PLAN-LIMIT',
            'email' => 'plan-limit@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);
    }

    private function subscribeSchool(array $limits = []): void
    {
        $plan = Plan::create(array_merge([
            'name' => 'Tiny Plan',
            'monthly_price' => 100,
            'yearly_price' => 1000,
            'max_buses' => 1,
            'max_students' => 1,
            'max_parents' => 1,
            'max_drivers' => 1,
            'max_routes' => 1,
            'max_devices' => 1,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ], $limits));

        Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    private function makeDriver(): Driver
    {
        return Driver::create([
            'school_id' => $this->school->id,
            'employee_id' => 'DR-LIMIT-'.uniqid(),
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-LIMIT-'.uniqid(),
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_bus_creation_blocked_at_plan_limit(): void
    {
        $this->subscribeSchool();

        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('buses.store'), [
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-002',
            'registration_number' => 'BA 1 KHA 5678',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors('plan_limit');
        $this->assertSame(1, Bus::where('school_id', $this->school->id)->count());
    }

    public function test_bus_creation_allowed_below_plan_limit(): void
    {
        $this->subscribeSchool();

        $response = $this->actingAs($this->admin)->post(route('buses.store'), [
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('buses.index'));
        $this->assertSame(1, Bus::where('school_id', $this->school->id)->count());
    }

    public function test_bus_creation_allowed_without_active_subscription(): void
    {
        $response = $this->actingAs($this->admin)->post(route('buses.store'), [
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('buses.index'));
        $this->assertSame(1, Bus::where('school_id', $this->school->id)->count());
    }

    public function test_driver_creation_blocked_at_plan_limit(): void
    {
        $this->subscribeSchool();
        $this->makeDriver();

        $response = $this->actingAs($this->admin)->post(route('drivers.store'), [
            'employee_id' => 'DR002',
            'first_name' => 'Hari',
            'last_name' => 'Bhandari',
            'gender' => 'Male',
            'date_of_birth' => '1991-01-01',
            'phone' => '9800000002',
            'email' => 'hari@example.com',
            'password' => 'password123',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-002',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'school_id' => $this->school->id,
        ]);

        $response->assertSessionHasErrors('plan_limit');
        $this->assertSame(1, Driver::where('school_id', $this->school->id)->count());
    }

    public function test_route_creation_blocked_at_plan_limit(): void
    {
        $this->subscribeSchool();

        Route::create([
            'school_id' => $this->school->id,
            'name' => 'Route One',
            'route_code' => 'RT-ONE',
            'start_location' => 'Gaushala',
            'end_location' => 'School',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('routes.store'), [
            'school_id' => $this->school->id,
            'name' => 'Route Two',
            'route_code' => 'RT-TWO',
            'route_type' => 'home_to_school',
            'start_location' => 'Baneshwor',
            'end_location' => 'School',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('plan_limit');
        $this->assertSame(1, Route::where('school_id', $this->school->id)->count());
    }

    public function test_parent_creation_blocked_at_plan_limit(): void
    {
        $this->subscribeSchool();
        $this->makeParentUser();

        $response = $this->actingAs($this->admin)->post(route('parents.store'), [
            'name' => 'Another Parent',
            'email' => 'parent2@example.com',
            'password' => 'password123',
            'school_id' => $this->school->id,
            'phone' => '9800000003',
            'address' => 'Kathmandu',
        ]);

        $response->assertSessionHasErrors('plan_limit');
        $this->assertSame(1, ParentProfile::where('school_id', $this->school->id)->count());
    }

    public function test_student_creation_blocked_at_plan_limit(): void
    {
        $this->subscribeSchool();

        $parent = $this->makeParentUser();

        Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'admission_no' => 'STD-001',
            'first_name' => 'Anita',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2012-01-01',
            'gender' => 'Female',
            'grade' => '7',
            'section' => 'A',
            'roll_no' => '01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('students.store'), [
            'admission_no' => 'STD-002',
            'parent_id' => $parent->id,
            'first_name' => 'Bikash',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2013-01-01',
            'gender' => 'Male',
            'grade' => '6',
            'section' => 'B',
            'roll_no' => '05',
            'school_id' => $this->school->id,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('plan_limit');
        $this->assertSame(1, Student::where('school_id', $this->school->id)->count());
    }

    public function test_limits_are_independent_per_entity(): void
    {
        $this->subscribeSchool();

        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
            'created_by' => $this->admin->id,
        ]);

        $this->makeDriver();

        $response = $this->actingAs($this->admin)->post(route('routes.store'), [
            'school_id' => $this->school->id,
            'name' => 'Route One',
            'route_code' => 'RT-ONE',
            'route_type' => 'home_to_school',
            'start_location' => 'Gaushala',
            'end_location' => 'School',
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('routes.index'));
        $this->assertSame(1, Route::where('school_id', $this->school->id)->count());
    }

    private function makeParentUser(): ParentProfile
    {
        $parentUser = User::factory()->create([
            'name' => 'Sita Sharma',
            'email' => 'sita'.uniqid().'@example.com',
            'school_id' => $this->school->id,
        ]);

        return ParentProfile::create([
            'user_id' => $parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Sita Sharma',
            'phone' => '9800000004',
            'address' => 'Kathmandu',
        ]);
    }
}