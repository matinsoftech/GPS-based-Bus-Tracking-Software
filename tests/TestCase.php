<?php

namespace Tests;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Give a school a usable subscription so its users are not blocked by the
     * `subscription` middleware during tests.
     */
    protected function activateSubscription(School $school, string $status = 'active'): Subscription
    {
        $plan = Plan::firstOrCreate(
            ['name' => 'Test Plan'],
            [
                'monthly_price' => 1999,
                'yearly_price' => 19999,
                'max_buses' => 999,
                'max_students' => 9999,
                'max_parents' => 9999,
                'max_drivers' => 999,
                'max_routes' => 999,
                'max_devices' => 999,
                'features' => [
                    'live_tracking' => true,
                    'parent_app' => true,
                    'notifications' => true,
                    'attendance' => true,
                    'reports' => true,
                    'analytics' => true,
                ],
                'is_active' => true,
            ]
        );

        return Subscription::create([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount' => 1999,
            'status' => $status,
            'starts_at' => now()->subDay(),
            'trial_ends_at' => $status === 'trialing' ? now()->addDays(14) : null,
            'ends_at' => now()->addMonth(),
        ]);
    }
}
