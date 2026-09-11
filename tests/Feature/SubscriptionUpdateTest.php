<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private School $school;

    private Plan $plan;

    private Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-SUB-UPDATE',
            'email' => 'sub-update@brightfuture.com',
            'phone' => '9800000200',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->plan = Plan::create([
            'name' => 'Standard Plan',
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

        $this->subscription = Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => $this->plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now(),
            'trial_ends_at' => null,
        ]);
    }

    public function test_changing_billing_cycle_to_yearly_recomputes_end_date_and_amount(): void
    {
        $response = $this->actingAs($this->admin)->put(
            route('subscriptions.update', $this->subscription),
            [
                'school_id' => $this->school->id,
                'plan_id' => $this->plan->id,
                'billing_cycle' => 'yearly',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('subscriptions.index'));

        $this->subscription->refresh();

        $this->assertSame('yearly', $this->subscription->billing_cycle);
        $this->assertSame((float) $this->plan->yearly_price, (float) $this->subscription->amount);
        $this->assertTrue(
            $this->subscription->starts_at->between(now()->subMinute(), now()->addMinute())
        );
        $this->assertTrue(
            $this->subscription->ends_at->between(
                now()->addYear()->subMinute(),
                now()->addYear()->addMinute()
            )
        );
    }

    public function test_changing_billing_cycle_to_monthly_recomputes_end_date_and_amount(): void
    {
        $this->subscription->update([
            'billing_cycle' => 'yearly',
            'amount' => $this->plan->yearly_price,
            'ends_at' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('subscriptions.update', $this->subscription),
            [
                'school_id' => $this->school->id,
                'plan_id' => $this->plan->id,
                'billing_cycle' => 'monthly',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('subscriptions.index'));

        $this->subscription->refresh();

        $this->assertSame('monthly', $this->subscription->billing_cycle);
        $this->assertSame((float) $this->plan->monthly_price, (float) $this->subscription->amount);
        $this->assertTrue(
            $this->subscription->ends_at->between(
                now()->addMonth()->subMinute(),
                now()->addMonth()->addMinute()
            )
        );
    }

    public function test_same_billing_cycle_edit_preserves_end_date(): void
    {
        $originalEndsAt = $this->subscription->ends_at->copy();

        $response = $this->actingAs($this->admin)->put(
            route('subscriptions.update', $this->subscription),
            [
                'school_id' => $this->school->id,
                'plan_id' => $this->plan->id,
                'billing_cycle' => 'monthly',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('subscriptions.index'));

        $this->subscription->refresh();

        $this->assertSame('monthly', $this->subscription->billing_cycle);
        $this->assertSame((float) $this->plan->monthly_price, (float) $this->subscription->amount);
        $this->assertTrue($this->subscription->ends_at->eq($originalEndsAt));
    }
}