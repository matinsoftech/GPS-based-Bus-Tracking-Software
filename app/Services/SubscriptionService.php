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
     * Statuses that still grant access to the product.
     */
    private const USABLE_STATUSES = ['trialing', 'active', 'past_due'];

    /**
     * The statuses that can still grant access and therefore may expire.
     */
    public function usableStatuses(): array
    {
        return self::USABLE_STATUSES;
    }

    /**
     * Grace window applied when a past_due subscription is created or edited.
     */
    private const PAST_DUE_GRACE_DAYS = 2;

    /**
     * Create a trial subscription for a school.
     */
    public function createTrial(
        School $school,
        Plan $plan,
        string $billingCycle = 'monthly',
        int $trialDays = 14
    ): Subscription {
        return $this->create($school, $plan, $billingCycle, 'trialing', $trialDays);
    }

    /**
     * Create a subscription for a school as `trialing` or `active`.
     */
    public function create(
        School $school,
        Plan $plan,
        string $billingCycle,
        string $status = 'trialing',
        int $trialDays = 14
    ): Subscription {
        if (! in_array($status, ['trialing', 'active', 'past_due'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected status is invalid.',
            ]);
        }

        if (! $plan->is_active) {
            throw ValidationException::withMessages([
                'plan_id' => 'The selected plan is inactive.',
            ]);
        }

        if ($school->subscriptions()
            ->whereIn('status', ['trialing', 'active'])
            ->exists()) {
            throw ValidationException::withMessages([
                'subscription' => 'The school already has an active subscription.',
            ]);
        }

        $amount = $this->getPlanPrice($plan, $billingCycle);

        return DB::transaction(function () use (
            $school,
            $plan,
            $billingCycle,
            $amount,
            $status,
            $trialDays
        ) {
            $startsAt = now();

            if ($status === 'trialing') {
                $trialEndsAt = $startsAt->copy()->addDays($trialDays);
                $endsAt = $trialEndsAt;
            } elseif ($status === 'past_due') {
                $trialEndsAt = null;
                $endsAt = $startsAt->copy()->addDays(self::PAST_DUE_GRACE_DAYS);
            } else {
                $trialEndsAt = null;
                $endsAt = $billingCycle === 'yearly'
                    ? $startsAt->copy()->addYear()
                    : $startsAt->copy()->addMonth();
            }

            return Subscription::create([
                'school_id' => $school->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'status' => $status,
                'starts_at' => $startsAt,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => $endsAt,
            ]);
        });
    }

    /**
     * Resolve the agreed price for a billing cycle.
     */
    private function getPlanPrice(Plan $plan, string $billingCycle): float
    {
        return match ($billingCycle) {
            'monthly' => (float) $plan->monthly_price,
            'yearly' => (float) $plan->yearly_price,
            default => throw ValidationException::withMessages([
                'billing_cycle' => 'Invalid billing cycle.',
            ]),
        };
    }

    /**
     * Determine whether a subscription is currently usable.
     */
    public function isActive(Subscription $subscription): bool
    {
        if (! in_array($subscription->status, self::USABLE_STATUSES, true)) {
            return false;
        }

        if ($subscription->ends_at === null) {
            return true;
        }

        return ! $subscription->ends_at->isPast();
    }

    /**
     * Check whether the subscription's plan provides a specific feature.
     */
    public function hasFeature(Subscription $subscription, string $feature): bool
    {
        if (! $this->isActive($subscription)) {
            return false;
        }

        return $subscription->plan?->hasFeature($feature) ?? false;
    }

    /**
     * Whether the school can create another bus under its plan limit.
     */
    public function canAddBus(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'buses', 'max_buses');
    }

    /**
     * Whether the school can create another student under its plan limit.
     */
    public function canAddStudent(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'students', 'max_students');
    }

    /**
     * Whether the school can create another parent under its plan limit.
     */
    public function canAddParent(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'parents', 'max_parents');
    }

    /**
     * Whether the school can create another driver under its plan limit.
     */
    public function canAddDriver(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'drivers', 'max_drivers');
    }

    /**
     * Whether the school can create another route under its plan limit.
     */
    public function canAddRoute(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'routes', 'max_routes');
    }

    /**
     * Whether the school can create another GPS device under its plan limit.
     */
    public function canAddDevice(Subscription $subscription): bool
    {
        return $this->canAddEntity($subscription, 'gpsDevices', 'max_devices');
    }

    /**
     * Upgrade/change the plan of a usable subscription.
     */
    public function upgrade(
        Subscription $subscription,
        Plan $plan,
        string $billingCycle
    ): Subscription {
        if (! $this->isActive($subscription)) {
            throw ValidationException::withMessages([
                'subscription' => 'The subscription is not active.',
            ]);
        }

        if (! $plan->is_active) {
            throw ValidationException::withMessages([
                'plan_id' => 'The selected plan is inactive.',
            ]);
        }

        $amount = $this->getPlanPrice($plan, $billingCycle);

        DB::transaction(function () use ($subscription, $plan, $billingCycle, $amount) {
            $subscription->update([
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
            ]);
        });

        return $subscription->refresh();
    }

    /**
     * Cancel a subscription without deleting its history.
     */
    public function cancel(Subscription $subscription): Subscription
    {
        if (! $this->isActive($subscription)) {
            throw ValidationException::withMessages([
                'subscription' => 'The subscription is not active.',
            ]);
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });

        return $subscription->refresh();
    }

    /**
     * Renew a subscription for another billing period on the same record.
     */
    public function renew(Subscription $subscription): Subscription
    {
        DB::transaction(function () use ($subscription) {
            $base = $subscription->ends_at ?? now();

            $endsAt = $subscription->billing_cycle === 'yearly'
                ? $base->copy()->addYear()
                : $base->copy()->addMonth();

            $subscription->update([
                'status' => 'active',
                'ends_at' => $endsAt,
                'cancelled_at' => null,
            ]);
        });

        return $subscription->refresh();
    }

    /**
     * Mark a subscription as expired when its end date has passed.
     */
    public function expireIfNeeded(Subscription $subscription): bool
    {
        $isExpired = in_array($subscription->status, self::USABLE_STATUSES, true)
            && $subscription->ends_at !== null
            && $subscription->ends_at->isPast();

        if ($isExpired) {
            DB::transaction(function () use ($subscription) {
                $subscription->update(['status' => 'expired']);
            });
        }

        return $isExpired;
    }

    /**
     * Whether the school can create another entity under its plan limit.
     */
    private function canAddEntity(Subscription $subscription, string $relation, string $column): bool
    {
        $plan = $subscription->plan;

        if ($plan === null) {
            return true;
        }

        $limit = $plan->{$column};

        if ($limit === null) {
            return true;
        }

        return $limit > $subscription->school->{$relation}()->count();
    }
}
