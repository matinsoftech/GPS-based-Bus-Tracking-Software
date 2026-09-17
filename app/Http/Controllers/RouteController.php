<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use App\Services\NazarTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    public function __construct(private readonly NazarTrackService $gpsService) {}
    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Route::with('school', 'activeTrip.bus');

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        $selectedSchool = ! $this->isSchoolLevelAdmin($user) ? $request->school_id : null;

        if ($selectedSchool) {
            $query->where('school_id', $selectedSchool);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('route_type', 'like', "%{$search}%")
                    ->orWhere('route_code', 'like', "%{$search}%")
                    ->orWhere('start_location', 'like', "%{$search}%")
                    ->orWhere('end_location', 'like', "%{$search}%");
            });
        }

        $routes = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = ! $this->isSchoolLevelAdmin($user)
            ? School::orderBy('name')->get()
            : collect();

        return view('routes.index', compact('routes', 'schools', 'selectedSchool'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create()
    {
        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        return view('routes.create', compact('school', 'schools'));
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code',
            'route_type' => 'required|in:home_to_school,school_to_home',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'estimated_distance' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        if (! $this->isSchoolLevelAdmin($user)) {
            $rules['school_id'] = 'required|exists:schools,id';
        }

        $validated = $request->validate($rules);

        if ($this->isSchoolLevelAdmin($user)) {
            $validated['school_id'] = $this->getUserSchoolId($user);
        }

        if ($error = app(\App\Services\PlanLimitService::class)->assertCreatable('routes', (int) $validated['school_id'])) {
            return back()->withInput()->withErrors(['plan_limit' => $error]);
        }

        $validated['is_active'] = $request->boolean('is_active');

        Route::create($validated);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route created successfully.');
    }

    /**
     * Display the specified route.
     */
    public function show(Route $route, Request $request)
    {
        $this->authorizeRoute($route);

        $tab = $request->query('tab', 'overview');

        $allowedTabs = ['overview', 'stops', 'map', 'trips'];

        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        $route->load(['school', 'activeTrip.bus.drivers', 'stops']);

        $tripCount = $route->trips()
            ->whereNotNull('started_at')
            ->whereIn('status', [\App\Models\Trip::STATUS_IN_PROGRESS, \App\Models\Trip::STATUS_COMPLETED])
            ->count();

        $trips = null;

        if ($tab === 'trips') {
            $trips = $route->trips()
                ->with(['bus', 'driver', 'school'])
                ->whereNotNull('started_at')
                ->whereIn('status', [\App\Models\Trip::STATUS_IN_PROGRESS, \App\Models\Trip::STATUS_COMPLETED])
                ->orderByDesc('started_at')
                ->paginate(10)
                ->withQueryString();
        }

        $activeBus = null;
        $activeDriver = null;
        $latestLocation = null;

        if ($tab === 'map') {
            $route->load(['activeTrip.bus.gpsDevice', 'activeTrip.driver']);

            $activeBus = $route->activeTrip?->bus;
            $activeDriver = $route->activeTrip?->driver;
            $latestLocation = $this->gpsService->locationPayload($activeBus)
                ?? $this->gpsService->lastKnownPayload($activeBus);
        }

        return view('routes.show', compact('route', 'tab', 'tripCount', 'trips', 'activeBus', 'activeDriver', 'latestLocation'));
    }

    /**
     * Display the trip history for the specified route.
     */
    public function trips(Route $route, Request $request)
    {
        $this->authorizeRoute($route);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'bus_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'trip_type' => ['nullable', Rule::in(array_keys(\App\Models\Trip::types()))],
            'status' => ['nullable', Rule::in(array_keys(\App\Models\Trip::statuses()))],
        ]);

        $trips = $route->trips()
            ->with(['bus', 'driver', 'school'])
            ->when(filled($validated['search'] ?? null), fn ($q) => $this->applyTripSearch($q, $validated['search']))
            ->when(filled($validated['bus_id'] ?? null), fn ($q) => $q->where('bus_id', $validated['bus_id']))
            ->when(filled($validated['driver_id'] ?? null), fn ($q) => $q->where('driver_id', $validated['driver_id']))
            ->when(filled($validated['trip_type'] ?? null), fn ($q) => $q->where('trip_type', $validated['trip_type']))
            ->when(filled($validated['status'] ?? null), fn ($q) => $q->where('status', $validated['status']))
            ->when(filled($validated['from'] ?? null), fn ($q) => $q->whereDate('started_at', '>=', Carbon::parse($validated['from'])->startOfDay()))
            ->when(filled($validated['to'] ?? null), fn ($q) => $q->whereDate('started_at', '<=', Carbon::parse($validated['to'])->endOfDay()))
            ->orderByDesc('started_at')
            ->paginate(10)
            ->withQueryString();

        $buses = $route->trips()
            ->with('bus')
            ->get()
            ->pluck('bus')
            ->filter()
            ->keyBy('id')
            ->values();

        $drivers = $route->trips()
            ->with('driver')
            ->get()
            ->pluck('driver')
            ->filter()
            ->keyBy('id')
            ->values();

        return view('routes.trips', compact('route', 'trips', 'buses', 'drivers'));
    }

    /**
     * Apply a keyword search across a trip's bus, driver and school.
     */
    private function applyTripSearch($query, string $term)
    {
        $needle = '%'.$term.'%';

        return $query->where(function ($query) use ($needle) {
            $query->whereHas('bus', fn ($q) => $q
                ->where('bus_number', 'like', $needle)
                ->orWhere('registration_number', 'like', $needle))
                ->orWhereHas('driver', fn ($q) => $q
                    ->where('first_name', 'like', $needle)
                    ->orWhere('last_name', 'like', $needle)
                    ->orWhere('phone', 'like', $needle)
                    ->orWhere('license_number', 'like', $needle))
                ->orWhereHas('school', fn ($q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('code', 'like', $needle));
        });
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit(Route $route)
    {
        $this->authorizeRoute($route);

        $route->load(['school']);

        return view('routes.edit', compact('route'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Route $route)
    {
        $this->authorizeRoute($route);

        $rules = [
            'name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code,'.$route->id,
            'route_type' => 'nullable|in:home_to_school,school_to_home',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'estimated_distance' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validated = $request->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | School cannot be changed through edit; keep the original assignment
        |--------------------------------------------------------------------------
        */

        $validated['school_id'] = $route->school_id;

        $validated['is_active'] = $request->boolean('is_active');

        $route->update($validated);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified route.
     */
    public function destroy(Route $route)
    {
        $this->authorizeRoute($route);

        $route->delete();

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route deleted successfully.');
    }

    /**
     * Make sure school-level admins can only access their own school's routes.
     */
    private function authorizeRoute(Route $route): void
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $route->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this route.');
            }
        }
    }

    private function isSchoolLevelAdmin(?User $user): bool
    {
        return $user && $user->hasAnyRole(['School Admin', 'Principal']);
    }

    private function getUserSchoolId(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        if (! empty($user->school_id)) {
            return (int) $user->school_id;
        }

        $schoolAdmin = SchoolAdmin::where('user_id', $user->id)->first();

        if ($schoolAdmin && ! empty($schoolAdmin->school_id)) {
            return (int) $schoolAdmin->school_id;
        }

        $school = School::where('principal_name', $user->name)
            ->orWhere('email', $user->email)
            ->first();

        return $school?->id;
    }
}
