<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\ProblemReport;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrincipalProblemReportTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private User $principal;

    private User $otherPrincipal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Sunrise Valley School',
            'code' => 'SCH-RP-1',
            'email' => 'sunrise@example.com',
            'phone' => '9800000700',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->otherSchool = School::create([
            'name' => 'Other School',
            'code' => 'SCH-RP-2',
            'email' => 'other@example.com',
            'phone' => '9800000701',
            'address' => 'Lalitpur',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Principal Alpha',
            'email' => 'principal.rp@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Alpha',
            'phone' => '9812345678',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->otherPrincipal = User::factory()->create([
            'name' => 'Principal Beta',
            'email' => 'principal.other@example.com',
            'school_id' => $this->otherSchool->id,
        ]);
        $this->otherPrincipal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->otherPrincipal->id,
            'school_id' => $this->otherSchool->id,
            'name' => 'Principal Beta',
            'phone' => '9812345679',
            'designation' => 'Principal',
            'address' => 'Lalitpur',
        ]);
    }

    private function makeReport(School $school, array $overrides = []): ProblemReport
    {
        $reporter = User::factory()->create(['school_id' => $school->id]);

        return ProblemReport::create(array_merge([
            'school_id' => $school->id,
            'reporter_user_id' => $reporter->id,
            'category' => 'breakdown',
            'severity' => 'medium',
            'status' => 'open',
            'description' => 'Bus reported a breakdown during the morning trip.',
        ], $overrides));
    }

    public function test_principal_sees_only_own_school_problem_reports(): void
    {
        $own = $this->makeReport($this->school);
        $ownSafety = $this->makeReport($this->school, ['category' => 'safety']);
        $other = $this->makeReport($this->otherSchool);

        Sanctum::actingAs($this->principal);

        $response = $this->getJson('/api/v1/principal/problem-reports');

        $response->assertOk()
            ->assertJsonPath('message', 'Problem reports.')
            ->assertJsonCount(2, 'data.problem_reports')
            ->assertJsonPath('data.problem_reports.0.id', $own->id)
            ->assertJsonPath('data.problem_reports.1.id', $ownSafety->id);
    }

    public function test_principal_cannot_view_other_school_problem_report(): void
    {
        $otherReport = $this->makeReport($this->otherSchool);

        Sanctum::actingAs($this->principal);

        $response = $this->getJson("/api/v1/principal/problem-reports/{$otherReport->id}");

        $response->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this problem report.');
    }

    public function test_principal_can_view_own_school_problem_report(): void
    {
        $report = $this->makeReport($this->school);

        Sanctum::actingAs($this->principal);

        $response = $this->getJson("/api/v1/principal/problem-reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Problem report details.')
            ->assertJsonPath('data.problem_report.id', $report->id)
            ->assertJsonPath('data.problem_report.status', 'open');
    }

    public function test_principal_can_filter_by_status(): void
    {
        $this->makeReport($this->school, ['status' => 'open']);
        $this->makeReport($this->school, ['status' => 'resolved', 'resolved_at' => now()]);

        Sanctum::actingAs($this->principal);

        $response = $this->getJson('/api/v1/principal/problem-reports?status=resolved');

        $response->assertOk()
            ->assertJsonCount(1, 'data.problem_reports')
            ->assertJsonPath('data.problem_reports.0.status', 'resolved');
    }

    public function test_principal_can_resolve_problem_report(): void
    {
        $report = $this->makeReport($this->school);

        Sanctum::actingAs($this->principal);

        $response = $this->postJson("/api/v1/principal/problem-reports/{$report->id}/resolve");

        $response->assertOk()
            ->assertJsonPath('message', 'Problem report resolved.')
            ->assertJsonPath('data.problem_report.status', 'resolved');

        $this->assertDatabaseHas('problem_reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);

        $this->assertNotNull($report->fresh()->resolved_at);
    }

    public function test_principal_cannot_resolve_other_school_problem_report(): void
    {
        $otherReport = $this->makeReport($this->otherSchool);

        Sanctum::actingAs($this->principal);

        $response = $this->postJson("/api/v1/principal/problem-reports/{$otherReport->id}/resolve");

        $response->assertForbidden();
    }

    public function test_cannot_resolve_already_resolved_report(): void
    {
        $report = $this->makeReport($this->school, [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        Sanctum::actingAs($this->principal);

        $response = $this->postJson("/api/v1/principal/problem-reports/{$report->id}/resolve");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Problem report is already resolved.');
    }

    public function test_non_principal_cannot_access(): void
    {
        $driver = User::factory()->create(['school_id' => $this->school->id]);
        $driver->assignRole('Driver');
        Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $driver->id,
            'employee_id' => 'DR-RP-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000702',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-RP-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $driver->id,
        ]);

        Sanctum::actingAs($driver);

        $response = $this->getJson('/api/v1/principal/problem-reports');

        $response->assertForbidden();
    }
}