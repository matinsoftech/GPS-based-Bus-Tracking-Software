<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\SchoolAdmin;
use App\Services\FleetMapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NazarTrackService;
use App\Notifications\BusStartedNotification;

class BusLocationController extends Controller
{
    public function __construct(
        private readonly FleetMapService $fleetMap,
        private readonly NazarTrackService $gpsService,
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
                ? $parent->children()->with(['route.stops', 'route.school', 'route.activeTrip.bus.gpsDevice', 'route.activeTrip.driver'])->get()
                : collect();

            $selectedChildId = $request->query('child_id');
            $selectedChild = $children->firstWhere('id', $selectedChildId)
                ?? $children->firstWhere('route_id', '!=', null)
                ?? $children->first();

            $route = $selectedChild?->route;
            $bus = $route?->activeTrip?->bus;
            $routes = $route ? collect([$route]) : collect();

            if ($route) {
                $route->load(['stops', 'school', 'activeTrip.bus', 'activeTrip.driver']);
            }

            $latestLocation = $bus ? $this->latestLocationForBus($bus) : null;

            $fleetBusIds = $children->pluck('route.activeTrip.bus')->flatten()->pluck('id')->filter();
            $fleetMap = $this->fleetMap->forSchool(null, $fleetBusIds);

            return view('bus_location.parent_bus_location', compact(
                'parent',
                'children',
                'selectedChild',
                'bus',
                'routes',
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
                ? $parent->children()->with('route.activeTrip.bus')->get()
                : collect();

            $selectedChildId = $request->query('child_id');

            // With a specific child selected, return the single normalized payload
            // consumed by the parent telemetry cards / stop timeline.
            if ($selectedChildId) {
                $selectedChild = $children->firstWhere('id', $selectedChildId);
                $bus = $selectedChild?->route?->activeTrip?->bus;

                return response()->json($this->latestLocationForBus($bus));
            }

            // Otherwise return the shared fleet map payload, scoped to the buses
            // of the parent's children so the shared map renders the same data.
            $fleetBusIds = $children->pluck('route.activeTrip.bus')->flatten()->pluck('id')->filter()->unique();

            return response()->json($this->fleetMap->forSchool(null, $fleetBusIds));
        }

        $busId = $request->query('bus_id');

        if ($busId && ($user->hasRole('School Admin') || $user->hasRole('Super Admin'))) {
            $schoolId = $user->school_id
                ?? SchoolAdmin::where('user_id', $user->id)->value('school_id');

            $bus = Bus::with(['routes.stops', 'drivers', 'school'])->find($busId);

            if (! $bus || ($schoolId && $bus->school_id != $schoolId)) {
                abort(403, 'You are not authorized to view this bus.');
            }

            return response()->json($this->latestLocationForBus($bus));
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
