<?php

namespace Tests\Feature\Console;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'Expiry Test School',
            'code' => 'SCH-EXP-1',
            'email' => 'expiry@example.com',
            'phone' => '9800000101',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);
    }

    private function makePlan(): Plan
    {
        return Plan::create([
            'name' => 'Expiry Plan',
            'monthly_price' => 100,
            'yearly_price' => 1000,
            'max_buses' => null,
            'max_students' => null,
            'max_parents' => null,
            'max_drivers' => null,
            'max_routes' => null,
            'max_devices' => null,
            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => true,
                'reports' => false,
                'analytics' => false,
            ],
            'is_active' => true,
        ]);
    }

    private function subscribe(string $status, array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'school_id' => $this->school->id,
            'plan_id' => $this->makePlan()->id,
            'billing_cycle' => 'monthly',
            'amount' => 100,
            'status' => $status,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ], $overrides));
    }

    public function test_active_subscription_with_past_end_date_is_expired(): void
    {
        $this->subscribe('active', ['ends_at' => now()->subDay()]);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(1, Subscription::where('status', 'expired')->count());
    }

    public function test_trialing_subscription_with_past_end_date_is_expired(): void
    {
        $this->subscribe('trialing', [
            'ends_at' => now()->subDay(),
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(1, Subscription::where('status', 'expired')->count());
    }

    public function test_future_subscription_is_left_untouched(): void
    {
        $subscription = $this->subscribe('active');

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame('active', $subscription->fresh()->status);
    }

    public function test_non_usable_statuses_are_left_untouched(): void
    {
        $this->subscribe('expired', ['ends_at' => now()->subDay()]);
        $this->subscribe('cancelled', ['ends_at' => now()->subDay()]);
        $this->subscribe('pending', ['ends_at' => now()->subDay()]);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(1, Subscription::where('status', 'expired')->count());
    }

    public function test_soft_deleted_subscription_is_left_untouched(): void
    {
        $subscription = $this->subscribe('active', ['ends_at' => now()->subDay()]);
        $subscription->delete();

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(0, Subscription::where('status', 'expired')->count());
    }

    public function test_command_is_idempotent(): void
    {
        $this->subscribe('active', ['ends_at' => now()->subDay()]);

        $this->artisan('subscriptions:expire')->assertSuccessful();
        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(1, Subscription::where('status', 'expired')->count());
    }
}
