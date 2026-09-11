<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrincipalSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $principal;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-PRINCIPAL-SUB',
            'email' => 'principal-sub@brightfuture.com',
            'phone' => '9800000100',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Principal Sharma',
            'email' => 'principal@brightfuture.com',
            'school_id' => $this->school->id,
        ]);
        $this->principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Sharma',
            'phone' => '9800000101',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);
    }

    private function subscribeSchool(string $status = 'active'): Plan
    {
        $plan = Plan::create([
            'name' => 'Standard Plan',
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'max_buses' => 20,
            'max_students' => 500,
            'max_parents' => 500,
            'max_drivers' => 25,
            'max_routes' => 20,
            'max_devices' => 20,
            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => true,
                'reports' => true,
                'analytics' => false,
            ],
            'is_active' => true,
        ]);

        Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => $status,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => now()->addDays(14),
        ]);

        return $plan;
    }

    public function test_school_admin_can_view_their_school_subscription_and_plan(): void
    {
        $this->subscribeSchool();

        $response = $this->actingAs($this->principal)->get(route('principal.subscription'));

        $response->assertOk()
            ->assertSee('Standard Plan')
            ->assertSee('Active')
            ->assertSee('days left')
            ->assertSee('Live Tracking')
            ->assertSee('Analytics')
            ->assertSee('monthly')
            ->assertSee('1,999.00')
            ->assertSee('20')
            ->assertSee('500');
    }

    public function test_school_admin_sees_expired_subscription_status(): void
    {
        $this->subscribeSchool('expired');

        $response = $this->actingAs($this->principal)->get(route('principal.subscription'));

        $response->assertOk()
            ->assertSee('Standard Plan')
            ->assertSee('Expired');
    }

    public function test_school_admin_sees_empty_state_when_no_subscription(): void
    {
        $response = $this->actingAs($this->principal)->get(route('principal.subscription'));

        $response->assertOk()
            ->assertSee('No Subscription Yet')
            ->assertSee('Contact your administrator');
    }
}