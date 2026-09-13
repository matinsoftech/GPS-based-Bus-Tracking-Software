<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\GpsDevice;
use App\Models\ParentProfile;
use App\Models\Plan;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Plan $plan;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SubscriptionService::class);

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-SUB-SERVICE',
            'email' => 'sub-service@brightfuture.com',
            'phone' => '9800000300',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->plan = $this->makePlan([
            'name' => 'Standard Plan',
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'max_buses' => 2,
            'max_students' => 10,
            'max_parents' => 5,
            'max_drivers' => 3,
            'max_routes' => 4,
            'max_devices' => 2,
            'features' => [
                'live_tracking' => true,
                'parent_app' => false,
                'notifications' => true,
                'attendance' => true,
                'reports' => false,
                'analytics' => false,
            ],
        ]);
    }

    /** 1. Create trial subscription */
    public function test_create_trial_creates_a_trialing_subscription(): void
    {
        $subscription = $this->service->createTrial($this->school, $this->plan, 'monthly');

        $this->assertSame('trialing', $subscription->status);
        $this->assertSame('monthly', $subscription->billing_cycle);
        $this->assertSame(1999.0, (float) $subscription->amount);
        $this->assertSame($this->school->id, $subscription->school_id);
        $this->assertSame($this->plan->id, $subscription->plan_id);

        $this->assertTrue($subscription->starts_at->isToday());
        $this->assertTrue($subscription->trial_ends_at->between(
            now()->addDays(14)->subMinute(),
            now()->addDays(14)->addMinute()
        ));
        $this->assertTrue($subscription->ends_at->eq($subscription->trial_ends_at));
    }

    public function test_create_trial_uses_yearly_price_and_custom_trial_days(): void
    {
        $subscription = $this->service->createTrial($this->school, $this->plan, 'yearly', 7);

        $this->assertSame('yearly', $subscription->billing_cycle);
        $this->assertSame(19999.0, (float) $subscription->amount);
        $this->assertTrue($subscription->trial_ends_at->between(
            now()->addDays(7)->subMinute(),
            now()->addDays(7)->addMinute()
        ));
    }

    public function test_create_active_creates_an_immediately_active_subscription(): void
    {
        $subscription = $this->service->create($this->school, $this->plan, 'monthly', 'active');

        $this->assertSame('active', $subscription->status);
        $this->assertSame('monthly', $subscription->billing_cycle);
        $this->assertSame(1999.0, (float) $subscription->amount);
        $this->assertSame($this->school->id, $subscription->school_id);
        $this->assertSame($this->plan->id, $subscription->plan_id);

        $this->assertTrue($subscription->starts_at->isToday());
        $this->assertNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->ends_at->between(
            now()->addMonth()->subMinute(),
            now()->addMonth()->addMinute()
        ));
    }

    public function test_create_active_yearly_sets_ends_at_to_one_year(): void
    {
        $subscription = $this->service->create($this->school, $this->plan, 'yearly', 'active');

        $this->assertSame('active', $subscription->status);
        $this->assertSame(19999.0, (float) $subscription->amount);
        $this->assertNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->ends_at->between(
            now()->addYear()->subMinute(),
            now()->addYear()->addMinute()
        ));
    }

    public function test_create_with_trialing_status_matches_create_trial(): void
    {
        $subscription = $this->service->create($this->school, $this->plan, 'monthly', 'trialing');

        $this->assertSame('trialing', $subscription->status);
        $this->assertSame(1999.0, (float) $subscription->amount);
        $this->assertTrue($subscription->trial_ends_at->between(
            now()->addDays(14)->subMinute(),
            now()->addDays(14)->addMinute()
        ));
        $this->assertTrue($subscription->ends_at->eq($subscription->trial_ends_at));
    }

    public function test_create_rejects_invalid_status(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The selected status is invalid.');

        $this->service->create($this->school, $this->plan, 'monthly', 'pending');
    }

    public function test_create_trial_rejects_inactive_plan(): void
    {
        $this->plan->update(['is_active' => false]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The selected plan is inactive.');

        $this->service->createTrial($this->school, $this->plan, 'monthly');
    }

    public function test_create_trial_rejects_invalid_billing_cycle(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid billing cycle.');

        $this->service->createTrial($this->school, $this->plan, 'weekly');
    }

    /** 2. Prevent duplicate active subscription */
    public function test_create_trial_rejects_when_school_already_has_active_subscription(): void
    {
        $this->service->createTrial($this->school, $this->plan, 'monthly');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The school already has an active subscription.');

        $this->service->createTrial($this->school, $this->plan, 'monthly');
    }

    /** 3. Check feature access */
    public function test_has_feature_returns_true_for_enabled_feature(): void
    {
        $subscription = $this->subscribe('active');

        $this->assertTrue($this->service->hasFeature($subscription, 'attendance'));
    }

    public function test_has_feature_returns_false_for_disabled_or_missing_feature(): void
    {
        $subscription = $this->subscribe('active');

        $this->assertFalse($this->service->hasFeature($subscription, 'reports'));
        $this->assertFalse($this->service->hasFeature($subscription, 'analytics'));
        $this->assertFalse($this->service->hasFeature($subscription, 'does_not_exist'));
    }

    public function test_has_feature_returns_false_when_subscription_is_not_active(): void
    {
        $subscription = $this->subscribe('cancelled');

        $this->assertFalse($this->service->hasFeature($subscription, 'attendance'));
    }

    /** 4. Check bus limit */
    public function test_can_add_bus_respects_plan_limit(): void
    {
        $subscription = $this->subscribe('active', ['max_buses' => 2]);

        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $this->assertTrue($this->service->canAddBus($subscription));

        Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'BUS-002',
            'registration_number' => 'BA 1 KHA 5678',
            'make' => 'Ashok Leyland',
            'capacity' => 40,
            'status' => 'Active',
        ]);

        $this->assertFalse($this->service->canAddBus($subscription));
    }

    public function test_can_add_bus_is_unlimited_when_limit_is_null(): void
    {
        $subscription = $this->subscribe('active', ['max_buses' => null]);

        $this->assertTrue($this->service->canAddBus($subscription));
    }

    /** 5. Check student limit */
    public function test_can_add_student_respects_plan_limit(): void
    {
        $subscription = $this->subscribe('active', ['max_students' => 10]);

        $parent = $this->makeParentProfile();

        for ($i = 0; $i < 10; $i++) {
            Student::create([
                'school_id' => $this->school->id,
                'parent_id' => $parent->id,
                'admission_no' => 'STD-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'first_name' => 'Student',
                'last_name' => (string) $i,
                'date_of_birth' => '2012-01-01',
                'gender' => 'Female',
                'grade' => '7',
                'section' => 'A',
                'is_active' => true,
            ]);
        }

        $this->assertFalse($this->service->canAddStudent($subscription));
    }

    public function test_can_add_device_respects_plan_limit(): void
    {
        $subscription = $this->subscribe('active', ['max_devices' => 1]);

        GpsDevice::create([
            'school_id' => $this->school->id,
            'device_name' => 'Device 1',
            'device_imei' => 'IMEI-001',
            'status' => 'active',
        ]);

        $this->assertFalse($this->service->canAddDevice($subscription));
    }

    /** 6. Cancel subscription */
    public function test_cancel_marks_subscription_cancelled_but_keeps_history(): void
    {
        $subscription = $this->subscribe('active');

        $this->service->cancel($subscription);
        $subscription->refresh();

        $this->assertSame('cancelled', $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertSame(1, Subscription::where('school_id', $this->school->id)->count());
    }

    public function test_cancel_rejects_inactive_subscription(): void
    {
        $subscription = $this->subscribe('expired');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The subscription is not active.');

        $this->service->cancel($subscription);
    }

    /** 7. Expire subscription */
    public function test_expire_if_needed_marks_past_subscription_expired(): void
    {
        $subscription = $this->subscribe('active', [], ['ends_at' => now()->subDay()]);

        $changed = $this->service->expireIfNeeded($subscription);
        $subscription->refresh();

        $this->assertTrue($changed);
        $this->assertSame('expired', $subscription->status);
    }

    public function test_active_subscription_is_not_expired(): void
    {
        $subscription = $this->subscribe('active');

        $this->assertFalse($this->service->expireIfNeeded($subscription));

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
    }

    public function test_is_active_false_when_ends_at_passed(): void
    {
        $subscription = $this->subscribe('active', [], ['ends_at' => now()->subDay()]);

        $this->assertFalse($this->service->isActive($subscription));
    }

    /** 8. Renew subscription */
    public function test_renew_extends_monthly_subscription_without_duplicate(): void
    {
        $subscription = $this->subscribe('past_due', [], [
            'ends_at' => now(),
            'cancelled_at' => now(),
        ]);

        $this->service->renew($subscription);
        $subscription->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertNull($subscription->cancelled_at);
        $this->assertTrue($subscription->ends_at->between(
            now()->addMonth()->subMinute(),
            now()->addMonth()->addMinute()
        ));

        $this->assertSame(1, Subscription::where('school_id', $this->school->id)->count());
    }

    public function test_renew_extends_yearly_subscription_by_one_year(): void
    {
        $subscription = $this->subscribe('active', [], [
            'billing_cycle' => 'yearly',
            'ends_at' => now(),
        ]);

        $this->service->renew($subscription);
        $subscription->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->ends_at->between(
            now()->addYear()->subMinute(),
            now()->addYear()->addMinute()
        ));
    }

    /** 9. Upgrade subscription */
    public function test_upgrade_changes_plan_cycle_and_amount(): void
    {
        $subscription = $this->subscribe('active');

        $newPlan = $this->makePlan([
            'name' => 'Premium Plan',
            'monthly_price' => 4999,
            'yearly_price' => 49999,
        ]);

        $upgraded = $this->service->upgrade($subscription, $newPlan, 'yearly');

        $this->assertSame($newPlan->id, $upgraded->plan_id);
        $this->assertSame('yearly', $upgraded->billing_cycle);
        $this->assertSame(49999.0, (float) $upgraded->amount);
        $this->assertSame(1, Subscription::where('school_id', $this->school->id)->count());
    }

    public function test_upgrade_rejects_inactive_subscription(): void
    {
        $subscription = $this->subscribe('cancelled');

        $newPlan = $this->makePlan(['name' => 'Premium Plan']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The subscription is not active.');

        $this->service->upgrade($subscription, $newPlan, 'monthly');
    }

    public function test_upgrade_rejects_inactive_target_plan(): void
    {
        $subscription = $this->subscribe('active');

        $newPlan = $this->makePlan(['name' => 'Inactive Plan', 'is_active' => false]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The selected plan is inactive.');

        $this->service->upgrade($subscription, $newPlan, 'monthly');
    }

    /**
     * Create a plan with sensible defaults.
     */
    private function makePlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Plan '.uniqid(),
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
        ], $overrides));
    }

    /**
     * Create a subscription for the test school.
     */
    private function subscribe(
        string $status,
        array $planOverrides = [],
        array $subscriptionOverrides = []
    ): Subscription {
        $plan = $this->makePlan($planOverrides);

        return Subscription::create(array_merge([
            'school_id' => $this->school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => $plan->monthly_price,
            'status' => $status,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ], $subscriptionOverrides));
    }

    /**
     * Create a parent profile for the test school.
     */
    private function makeParentProfile(): ParentProfile
    {
        $user = User::factory()->create([
            'name' => 'Sita Sharma',
            'email' => 'sita'.uniqid().'@example.com',
            'school_id' => $this->school->id,
        ]);

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'name' => 'Sita Sharma',
            'phone' => '9800000301',
            'address' => 'Kathmandu',
        ]);
    }
}
