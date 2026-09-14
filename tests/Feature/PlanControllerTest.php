<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanControllerTest extends TestCase
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
            'name' => 'Plan Test School',
            'code' => 'SCH-PLAN-1',
            'email' => 'plan-test@example.com',
            'phone' => '9800000450',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);
    }

    private function makePlan(string $name = 'Premium Plan'): Plan
    {
        return Plan::create([
            'name' => $name,
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'max_buses' => 20,
            'max_students' => 500,
            'max_parents' => 500,
            'max_drivers' => 25,
            'max_routes' => 20,
            'max_devices' => 20,
            'features' => ['live_tracking' => true],
            'is_active' => true,
        ]);
    }

    private function attachSubscription(Plan $plan, string $status = 'active'): Subscription
    {
        return Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => $plan->monthly_price,
            'status' => $status,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function test_plan_with_subscriptions_can_be_soft_deleted(): void
    {
        $plan = $this->makePlan();
        $this->attachSubscription($plan, 'active');

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'))
            ->assertSessionHas('success', 'Plan deleted successfully.');

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    }

    public function test_plan_with_invoices_can_be_soft_deleted(): void
    {
        $plan = $this->makePlan();
        $subscription = $this->attachSubscription($plan, 'active');

        Invoice::create([
            'invoice_number' => 'INV-PLAN-0001',
            'school_id' => $this->school->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => '1999.00',
            'currency' => 'NPR',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'issued_at' => now()->subWeek(),
            'due_at' => now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'))
            ->assertSessionHas('success', 'Plan deleted successfully.');

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    }

    public function test_plan_with_soft_deleted_subscription_can_be_soft_deleted(): void
    {
        $plan = $this->makePlan();
        $this->attachSubscription($plan, 'active')->delete();

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'))
            ->assertSessionHas('success', 'Plan deleted successfully.');

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    }

    public function test_plan_with_soft_deleted_invoice_can_be_soft_deleted(): void
    {
        $plan = $this->makePlan();
        $subscription = $this->attachSubscription($plan, 'active');

        $invoice = Invoice::create([
            'invoice_number' => 'INV-PLAN-0002',
            'school_id' => $this->school->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => '1999.00',
            'currency' => 'NPR',
            'billing_period_start' => now()->subMonth(),
            'billing_period_end' => now(),
            'issued_at' => now()->subWeek(),
            'due_at' => now()->addDays(7),
            'status' => 'unpaid',
        ]);
        $invoice->delete();

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'))
            ->assertSessionHas('success', 'Plan deleted successfully.');

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    }

    public function test_plan_without_references_can_be_deleted(): void
    {
        $plan = $this->makePlan();

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'))
            ->assertSessionHas('success', 'Plan deleted successfully.');

        $this->assertSoftDeleted('plans', ['id' => $plan->id]);
    }

    public function test_soft_deleted_plan_still_resolves_on_subscription_and_invoice(): void
    {
        $plan = $this->makePlan();
        $subscription = $this->attachSubscription($plan, 'active');

        $this->actingAs($this->admin)
            ->delete(route('plans.destroy', $plan));

        $subscription->refresh();
        $this->assertNotNull($subscription->plan);
        $this->assertSame($plan->id, $subscription->plan->id);
    }

    public function test_index_shows_delete_button_for_plans_in_use(): void
    {
        $inUse = $this->makePlan('In Use Plan');
        $this->attachSubscription($inUse, 'active');

        $response = $this->actingAs($this->admin)->get(route('plans.index'));

        $response->assertOk()
            ->assertSee("open-delete-modal', { id: {$inUse->id}", false);
    }

    public function test_index_shows_delete_button_for_unused_plans(): void
    {
        $unused = $this->makePlan('Unused Plan');

        $response = $this->actingAs($this->admin)->get(route('plans.index'));

        $response->assertOk()
            ->assertSee("open-delete-modal', { id: {$unused->id}", false);
    }
}
