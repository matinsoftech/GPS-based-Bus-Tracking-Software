<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProblemReportCreateTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $driverUser;

    private User $parentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Sunrise Valley School',
            'code' => 'SCH-RPT-1',
            'email' => 'sunrise@example.com',
            'phone' => '9800000600',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create([
            'name' => 'Driver Ramesh',
            'email' => 'driver.rpt@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->driverUser->assignRole('Driver');

        Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-RPT-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000601',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-RPT-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);

        $this->parentUser = User::factory()->create([
            'name' => 'Hari Bahadur',
            'email' => 'parent.rpt@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->parentUser->assignRole('Parent');

        ParentProfile::create([
            'user_id' => $this->parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9812345600',
            'address' => 'Chabahil, Kathmandu',
        ]);
    }

    public function test_driver_can_report_a_problem(): void
    {
        Sanctum::actingAs($this->driverUser);

        $response = $this->postJson('/api/v1/problem-reports', [
            'category' => 'breakdown',
            'severity' => 'high',
            'description' => 'Bus engine overheated and stopped on the way.',
            'latitude' => 27.712,
            'longitude' => 85.324,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Problem report submitted.')
            ->assertJsonPath('data.problem_report.school.id', $this->school->id)
            ->assertJsonPath('data.problem_report.reporter.id', $this->driverUser->id)
            ->assertJsonPath('data.problem_report.category', 'breakdown')
            ->assertJsonPath('data.problem_report.status', 'open');

        $this->assertDatabaseHas('problem_reports', [
            'school_id' => $this->school->id,
            'reporter_user_id' => $this->driverUser->id,
            'category' => 'breakdown',
            'severity' => 'high',
            'status' => 'open',
        ]);
    }

    public function test_parent_can_report_a_problem(): void
    {
        Sanctum::actingAs($this->parentUser);

        $response = $this->postJson('/api/v1/problem-reports', [
            'category' => 'safety',
            'description' => 'Bystander harassing students at the bus stop.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Problem report submitted.')
            ->assertJsonPath('data.problem_report.school.id', $this->school->id)
            ->assertJsonPath('data.problem_report.category', 'safety')
            ->assertJsonPath('data.problem_report.severity', 'medium');

        $this->assertDatabaseHas('problem_reports', [
            'school_id' => $this->school->id,
            'reporter_user_id' => $this->parentUser->id,
        ]);
    }

    public function test_description_is_required(): void
    {
        Sanctum::actingAs($this->driverUser);

        $response = $this->postJson('/api/v1/problem-reports', [
            'category' => 'other',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');
    }

    public function test_category_must_be_valid(): void
    {
        Sanctum::actingAs($this->driverUser);

        $response = $this->postJson('/api/v1/problem-reports', [
            'category' => 'not-a-category',
            'description' => 'This is a long enough description.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('category');
    }

    public function test_unauthenticated_cannot_report(): void
    {
        $response = $this->postJson('/api/v1/problem-reports', [
            'description' => 'This report should not be created.',
        ]);

        $response->assertUnauthorized();
    }
}