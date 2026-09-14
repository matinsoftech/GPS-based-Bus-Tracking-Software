<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly InvoiceService $invoices
    ) {}

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

        $subscription = $this->subscriptions->create(
            $school,
            $plan,
            $request->billing_cycle,
            $request->status ?? 'trialing'
        );

        if ($subscription->status === 'active') {
            $this->invoices->generateForSubscription($subscription);
        }

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

        $previousBillingCycle = $subscription->billing_cycle;

        $this->subscriptions->upgrade($subscription, $plan, $request->billing_cycle);
        $subscription->refresh();

        $status = $request->status ?? $subscription->status;

        $payload = [
            'status' => $status,
            'trial_ends_at' => $status === 'trialing'
                ? ($subscription->trial_ends_at ?? now()->addDays(14))
                : $subscription->trial_ends_at,
        ];

        if ($request->billing_cycle !== $previousBillingCycle) {
            $payload['starts_at'] = now();
            $payload['ends_at'] = $request->billing_cycle === 'monthly'
                ? $payload['starts_at']->copy()->addMonth()
                : $payload['starts_at']->copy()->addYear();
        } else {
            $payload['starts_at'] = $subscription->starts_at ?? now();
        }

        $subscription->update($payload);

        if ($status === 'active') {
            $this->invoices->generateForSubscription($subscription->refresh());
        }

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
