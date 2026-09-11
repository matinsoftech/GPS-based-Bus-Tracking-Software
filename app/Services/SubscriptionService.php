<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    /**
     * Create a trial subscription for a school.
     */
    public function createTrial(
        School $school,
        Plan $plan,
        string $billingCycle = 'monthly',
        int $trialDays = 14
    ): Subscription {
        if (!$plan->is_active) {
            throw ValidationException::withMessages([
                'plan_id' => 'This plan is currently unavailable.',
            ]);
        }

        if ($school->activeSubscription()->exists()) {
            throw ValidationException::withMessages([
                'subscription' => 'The school already has an active subscription.',
            ]);
        }

        $amount = $this->getPlanPrice($plan, $billingCycle);

        $startsAt = now();
        $trialEndsAt = now()->addDays($trialDays);

        return DB::transaction(function () use (
            $school,
            $plan,
            $billingCycle,
            $amount,
            $startsAt,
            $trialEndsAt
        ) {
            return Subscription::create([
                'school_id' => $school->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'status' => 'trialing',
                'starts_at' => $startsAt,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => $trialEndsAt,
            ]);
        });
    }

    /**
     * Get monthly/yearly price from plan.
     */
    private function getPlanPrice(
        Plan $plan,
        string $billingCycle
    ): float {
        return match ($billingCycle) {
            'monthly' => (float) $plan->monthly_price,
            'yearly' => (float) $plan->yearly_price,

            default => throw ValidationException::withMessages([
                'billing_cycle' => 'Invalid billing cycle.',
            ]),
        };
    }
}