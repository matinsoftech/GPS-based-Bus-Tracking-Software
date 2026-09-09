<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\SchoolAdmin;
use App\Notifications\BusStartedNotification;
use App\Services\FleetMapService;
use App\Services\NazarTrackService;
use App\Services\StopArrivalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusLocationController extends Controller
{
    public function __construct(
        private readonly FleetMapService $fleetMap,
        private readonly NazarTrackService $gpsService,
        private readonly StopArrivalService $stopArrivals,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Parent')) {
            $parent = ParentProfile::where('user_id', $user->id)->first();

            $children = $parent
                ? $parent->children()->with(['routes.stops', 'routes.school', 'routes.activeTrip.bus.gpsDevice', 'routes.activeTrip.driver'])->get()
                : collect();

            $selectedChildId = $request->query('child_id');
            $selectedChild = $children->firstWhere('id', $selectedChildId)
                ?? $children->firstWhere('routes', fn ($routes) => $routes->isNotEmpty())
                ?? $children->first();

            $assignedRoutes = $selectedChild?->routes ?? collect();
            $routes = $assignedRoutes;

            $requestedRouteId = $request->query('route_id');
            $route = $requestedRouteId
                ? ($assignedRoutes->firstWhere('id', $requestedRouteId) ?? null)
                : null;

            $route = $route
                ?? $assignedRoutes->first(fn ($r) => $r->activeTrip)
                ?? $assignedRoutes->first();

            $bus = $route?->activeTrip?->bus;

            $studentStops = collect();

            if ($selectedChild && $route) {
                $studentStops = $selectedChild->stops()
                    ->where('route_stops.route_id', $route->id)
                    ->get();
            }

            $studentStopIds = $studentStops->pluck('id')->all();

            if ($route) {
                $route->load(['stops', 'school', 'activeTrip.bus', 'activeTrip.driver']);
            }

            $latestLocation = $bus ? $this->latestLocationForBus($bus) : null;

            // Always include the assigned route so the parent sees the route path
            // and stops even before a trip starts; the live bus marker/telemetry
            // only appear once an active trip provides a bus.
            $fleetMap = $route
                ? $this->fleetMap->forRoute($route, $latestLocation)
                : ['buses' => [], 'routes' => [], 'summary' => [], 'school' => null, 'updated_at' => now()->toIso8601String()];

            return view('bus_location.parent_bus_location', compact(
                'parent',
                'children',
                'selectedChild',
                'route',
                'bus',
                'routes',
                'studentStops',
                'studentStopIds',
                'latestLocation',
                'fleetMap'
            ));
        }

        $allowedBusIds = null;
        $schoolId = null;

        if ($user->hasRole('Driver')) {
            $driver = Driver::where('user_id', $user->id)->first();
            $allowedBusIds = $driver ? $driver->buses()->pluck('id') : collect();
        } elseif ($user->hasRole('School Admin')) {
            $schoolId = $user->school_id
                ?? SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        $fleetMap = $this->fleetMap->forSchool($schoolId, $allowedBusIds);

        return view('bus_location.bus_location', [
            'fleetMap' => $fleetMap,
        ]);
    }

    /**
     * JSON endpoint used by the live tracking page to poll the latest GPS fix.
     *
     * - Parents receive a single normalized device payload for the selected child's bus.
     * - School Admin / Super Admin may pass `bus_id` to receive a single normalized
     *   payload for that specific bus (used by the bus detail view).
     * - Otherwise (Super Admin, School Admin, Driver) receive the full fleet map
     *   payload for the buses/school they are allowed to see.
     */
    public function latestJson(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Parent')) {
            $parent = ParentProfile::where('user_id', $user->id)->first();

            $children = $parent
                ? $parent->children()->with('routes.activeTrip.bus')->get()
                : collect();

            $selectedChildId = $request->query('child_id');

            // With a specific child selected, return the payload for the selected
            // route's bus: the normalized device payload (consumed by the parent
            // telemetry cards / stop timeline) by default, or the fleet map payload
            // (consumed by the map refresh) when the caller passes view=fleet.
            if ($selectedChildId) {
                $selectedChild = $children->firstWhere('id', $selectedChildId);

                $assignedRoutes = $selectedChild?->routes ?? collect();

                $requestedRouteId = $request->query('route_id');
                $route = $requestedRouteId
                    ? ($assignedRoutes->firstWhere('id', $requestedRouteId) ?? null)
                    : null;

                $route = $route
                    ?? $assignedRoutes->first(fn ($r) => $r->activeTrip)
                    ?? $assignedRoutes->first();

                $bus = $route?->activeTrip?->bus;

                $payload = $this->latestLocationForBus($bus);

                if ($request->query('view') === 'fleet') {
                    return response()->json(
                        $route
                            ? $this->fleetMap->forRoute($route, $payload)
                            : ['buses' => [], 'routes' => [], 'summary' => [], 'school' => null, 'updated_at' => now()->toIso8601String()]
                    );
                }

                return response()->json($this->stopArrivals->withStopContext($bus, $payload));
            }

            // Otherwise return the shared fleet map payload, scoped to the buses
            // of the parent's children so the shared map renders the same data.
            $fleetBusIds = $children
                ->flatMap(fn ($child) => $child->routes->pluck('activeTrip.bus'))
                ->filter()
                ->pluck('id')
                ->unique();

            return response()->json($this->fleetMap->forSchool(null, $fleetBusIds));
        }

        $busId = $request->query('bus_id');

        if ($busId && ($user->hasRole('School Admin') || $user->hasRole('Super Admin'))) {
            $schoolId = $user->school_id
                ?? SchoolAdmin::where('user_id', $user->id)->value('school_id');

            $bus = Bus::with(['drivers', 'school'])->find($busId);

            if (! $bus || ($schoolId && $bus->school_id != $schoolId)) {
                abort(403, 'You are not authorized to view this bus.');
            }

            $payload = $this->latestLocationForBus($bus);

            return response()->json($this->stopArrivals->withStopContext($bus, $payload));
        }

        $allowedBusIds = null;
        $schoolId = null;

        if ($user->hasRole('Driver')) {
            $driver = Driver::where('user_id', $user->id)->first();
            $allowedBusIds = $driver ? $driver->buses()->pluck('id') : collect();
        } elseif ($user->hasRole('School Admin')) {
            $schoolId = $user->school_id
                ?? SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        return response()->json($this->fleetMap->forSchool($schoolId, $allowedBusIds));
    }

    /**
     * Build the normalized "latest GPS" payload consumed by the live map page.
     */
    private function latestLocationForBus(?Bus $bus): ?array
    {
        return $this->gpsService->locationPayload($bus);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BusLocation $busLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusLocation $busLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusLocation $busLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusLocation $busLocation)
    {
        //
    }

    /**
     * Test the notification system.
     */
    // public function testNotification()
    // {
    //     $user = auth()->user();

    //     $bus = Bus::first();

    //     if (!$bus) {
    //         return back()->with('error', 'No bus found.');
    //     }

    //     $user->notify(
    //         new BusStartedNotification($bus)
    //     );

    //     return back()->with('success', 'Notification created.');
    // }
}
