<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $plans = Plan::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('plans.index', compact('plans', 'search'));
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(StorePlanRequest $request)
    {
        Plan::create([
            'name' => $request->name,
            'monthly_price' => $request->monthly_price,
            'yearly_price' => $request->yearly_price,
            'max_buses' => $request->max_buses,
            'max_students' => $request->max_students,
            'max_parents' => $request->max_parents,
            'max_drivers' => $request->max_drivers,
            'max_routes' => $request->max_routes,
            'max_devices' => $request->max_devices,
            'features' => $this->buildFeatures($request),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        return view('plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan->update([
            'name' => $request->name,
            'monthly_price' => $request->monthly_price,
            'yearly_price' => $request->yearly_price,
            'max_buses' => $request->max_buses,
            'max_students' => $request->max_students,
            'max_parents' => $request->max_parents,
            'max_drivers' => $request->max_drivers,
            'max_routes' => $request->max_routes,
            'max_devices' => $request->max_devices,
            'features' => $this->buildFeatures($request),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('plans.index')->with('success', 'Plan deleted successfully.');
    }

    private function buildFeatures(Request $request): array
    {
        return [
            'live_tracking' => $request->boolean('feature_live_tracking'),
            'parent_app' => $request->boolean('feature_parent_app'),
            'notifications' => $request->boolean('feature_notifications'),
            'attendance' => $request->boolean('feature_attendance'),
            'reports' => $request->boolean('feature_reports'),
            'analytics' => $request->boolean('feature_analytics'),
        ];
    }
}
