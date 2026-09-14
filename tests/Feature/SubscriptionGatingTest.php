<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Invoice;
use App\Models\ParentProfile;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionGatingTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $principal;

    private User $driverUser;

    private User $parentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Gated Valley School',
            'code' => 'SCH-GATE-1',
            'email' => 'gated@example.com',
            'phone' => '9800000900',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Gate Principal',
            'email' => 'gate-principal@example.com',
            'school_id' => $this->school->id,
        ]);
        $this->principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Gate Principal',
            'phone' => '9800000901',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->driverUser = User::factory()->create();
        $this->driverUser->assignRole('Driver');

        Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-GATE-1',
            'first_name' => 'Gate',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000902',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-GATE-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->principal->id,
        ]);

        $this->parentUser = User::factory()->create();
        $this->parentUser->assignRole('Parent');

        ParentProfile::create([
            'user_id' => $this->parentUser->id,
            'school_id' => $this->school->id,
            'name' => 'Gate Parent',
            'phone' => '9800000903',
            'address' => 'Kathmandu',
        ]);
    }

    private function subscribe(string $status = 'active', string $endsAt = 'future'): Subscription
    {
        $plan = Plan::create([
            'name' => 'Gating Plan',
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
                'analytics' => true,
            ],
            'is_active' => true,
        ]);

        return Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => $status,
            'starts_at' => now()->subDay(),
            'ends_at' => $endsAt === 'past' ? now()->subDay() : now()->addMonth(),
            'trial_ends_at' => $status === 'trialing' ? now()->addDays(14) : null,
        ]);
    }

    public function test_school_admin_without_subscription_is_redirected_to_subscription_page(): void
    {
        $response = $this->actingAs($this->principal)->get(route('buses.index'));

        $response->assertRedirect(route('principal.subscription'));
    }

    public function test_school_admin_with_active_subscription_can_access_modules(): void
    {
        $this->subscribe('active');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertOk();

        $this->actingAs($this->principal)
            ->get(route('students.index'))
            ->assertOk();

        $this->actingAs($this->principal)
            ->get(route('principal.dashboard'))
            ->assertOk();
    }

    public function test_school_admin_with_trialing_subscription_can_access_modules(): void
    {
        $this->subscribe('trialing');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertOk();
    }

    public function test_school_admin_with_expired_subscription_is_blocked(): void
    {
        $this->subscribe('expired');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertRedirect(route('principal.subscription'));
    }

    public function test_school_admin_with_cancelled_subscription_is_blocked(): void
    {
        $this->subscribe('cancelled');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertRedirect(route('principal.subscription'));
    }

    public function test_school_admin_with_future_past_due_subscription_can_access_modules(): void
    {
        $this->subscribe('past_due', 'future');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertOk();
    }

    public function test_school_admin_with_lapsed_past_due_subscription_is_blocked(): void
    {
        $this->subscribe('past_due', 'past');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertRedirect(route('principal.subscription'));
    }

    public function test_active_subscription_with_past_end_date_is_blocked(): void
    {
        $this->subscribe('active', 'past');

        $this->actingAs($this->principal)
            ->get(route('buses.index'))
            ->assertRedirect(route('principal.subscription'));
    }

    public function test_inactive_school_admin_can_view_own_invoices(): void
    {
        $subscription = $this->subscribe('expired');

        $invoice = Invoice::create([
            'invoice_number' => 'INV-GATE-0001',
            'school_id' => $this->school->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'billing_cycle' => 'monthly',
            'amount' => '1999.00',
            'currency' => 'NPR',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'issued_at' => now()->subWeek(),
            'due_at' => now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $this->actingAs($this->principal)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_number);

        $this->actingAs($this->principal)
            ->get(route('invoices.show', $invoice))
            ->assertOk();

        $this->actingAs($this->principal)
            ->get(route('invoices.print', $invoice))
            ->assertOk();
    }

    public function test_inactive_school_admin_cannot_see_other_schools_invoices(): void
    {
        $otherSchool = School::create([
            'name' => 'Riverside School',
            'code' => 'SCH-GATE-2',
            'email' => 'riverside@example.com',
            'phone' => '9800000904',
            'address' => 'Lalitpur',
            'status' => 'active',
        ]);

        $otherPlan = Plan::create([
            'name' => 'Other Gating Plan',
            'monthly_price' => 999,
            'yearly_price' => 9999,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ]);

        $otherSubscription = Subscription::create([
            'school_id' => $otherSchool->id,
            'plan_id' => $otherPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 999,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
        ]);

        $otherInvoice = Invoice::create([
            'invoice_number' => 'INV-GATE-0002',
            'school_id' => $otherSchool->id,
            'subscription_id' => $otherSubscription->id,
            'plan_id' => $otherPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => '999.00',
            'currency' => 'NPR',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'issued_at' => now()->subWeek(),
            'due_at' => now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $this->actingAs($this->principal)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertDontSee($otherInvoice->invoice_number);

        $this->actingAs($this->principal)
            ->get(route('invoices.show', $otherInvoice))
            ->assertForbidden();
    }

    public function test_super_admin_has_access_without_subscription(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin)
            ->get(route('buses.index'))
            ->assertOk();
    }

    public function test_driver_without_subscription_is_redirected_to_inactive_page(): void
    {
        $this->actingAs($this->driverUser)
            ->get(route('driver.dashboard'))
            ->assertRedirect(route('subscription.inactive'));
    }

    public function test_driver_with_active_subscription_can_access_page(): void
    {
        $this->subscribe('active');

        $this->actingAs($this->driverUser)
            ->get(route('driver.dashboard'))
            ->assertOk();
    }

    public function test_parent_without_subscription_is_redirected_to_inactive_page(): void
    {
        $this->actingAs($this->parentUser)
            ->get(route('parent.dashboard'))
            ->assertRedirect(route('subscription.inactive'));
    }

    public function test_profile_and_notifications_stay_reachable_when_blocked(): void
    {
        $this->actingAs($this->principal)
            ->get(route('profile.edit'))
            ->assertOk();

        $this->actingAs($this->principal)
            ->get(route('principal.subscription'))
            ->assertOk();

        $this->actingAs($this->principal)
            ->get(route('notifications.index'))
            ->assertOk();
    }

    public function test_inactive_page_renders_for_driver(): void
    {
        $this->actingAs($this->driverUser)
            ->get(route('subscription.inactive'))
            ->assertOk()
            ->assertSee('Service Paused');
    }

    public function test_sidebar_hides_products_for_inactive_school(): void
    {
        $response = $this->actingAs($this->driverUser)
            ->get(route('subscription.inactive'));

        $response->assertOk()
            ->assertDontSee('Live Tracking')
            ->assertDontSee('Trip Management');
    }

    public function test_api_returns_403_for_inactive_school(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/dashboard')
            ->assertStatus(403);
    }

    public function test_api_403_marks_subscription_as_required(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/dashboard')
            ->assertStatus(403)
            ->assertJson([
                'message' => "Your school's subscription is not active.",
                'subscription_required' => true,
                'status' => 'inactive',
            ])
            ->assertDontSee('exception')
            ->assertDontSee('trace');
    }

    public function test_api_account_endpoints_stay_reachable_when_blocked(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/auth/me')->assertOk();
        $this->postJson('/api/v1/auth/logout')->assertOk();
        $this->putJson('/api/v1/auth/change-password', [
            'current_password' => 'wrong-current-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);
    }

    public function test_api_notification_endpoints_stay_reachable_when_blocked(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/notifications')->assertOk();
        $this->getJson('/api/v1/notifications/unread-count')->assertOk();
        $this->postJson('/api/v1/notifications/read-all')->assertOk();
    }

    public function test_api_allows_active_school(): void
    {
        $this->subscribe('active');

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/dashboard')
            ->assertOk();
    }
}
