<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $subscriptions = Subscription::query()
            ->with(['school', 'plan'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('school', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('plan', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('subscriptions.index', compact('subscriptions', 'search'));
    }

    public function create(Request $request)
    {
        $schools = School::orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $selectedSchool = $request->school;

        return view('subscriptions.create', compact('schools', 'plans', 'selectedSchool'));
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $school = School::findOrFail($request->school_id);

        $plan = Plan::where('id', $request->plan_id)
            ->where('is_active', true)
            ->firstOrFail();

        if ($school->activeSubscription) {
            return back()->with(
                'error',
                "{$school->name} already has an active subscription."
            );
        }

        $status = $request->status ?? 'trialing';
        $amount = $request->billing_cycle === 'monthly'
            ? $plan->monthly_price
            : $plan->yearly_price;

        $startsAt = now();
        $endsAt = $request->billing_cycle === 'monthly'
            ? $startsAt->copy()->addMonth()
            : $startsAt->copy()->addYear();

        Subscription::create([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'amount' => $amount,
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $status === 'trialing' ? $startsAt->copy()->addDays(14) : null,
        ]);

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function edit(Subscription $subscription)
    {
        $schools = School::orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('subscriptions.edit', compact('subscription', 'schools', 'plans'));
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $school = School::findOrFail($request->school_id);

        $plan = Plan::where('id', $request->plan_id)
            ->where('is_active', true)
            ->firstOrFail();

        if (
            $subscription->school_id !== $school->id
            && $school->activeSubscription
        ) {
            return back()->with(
                'error',
                "{$school->name} already has an active subscription."
            );
        }

        $status = $request->status ?? $subscription->status;
        $amount = $request->billing_cycle === 'monthly'
            ? $plan->monthly_price
            : $plan->yearly_price;

        $subscription->update([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'amount' => $amount,
            'status' => $status,
            'starts_at' => $subscription->starts_at ?? now(),
            'trial_ends_at' => $status === 'trialing'
                ? ($subscription->trial_ends_at ?? now()->addDays(14))
                : $subscription->trial_ends_at,
        ]);

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }
}
