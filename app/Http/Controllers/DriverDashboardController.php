<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\Trip;
use App\Services\FleetMapService;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function __construct(private readonly FleetMapService $fleetMap) {}

    /**
     * Show the driver dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $driver = Driver::where('user_id', $user->id)->first();

        if (! $driver) {
            return view('driverDashboard', [
                'user' => $user,
                'driver' => null,
                'buses' => collect(),
                'fleetMap' => $this->fleetMap->forSchool(null, collect()),
                'fleetMapRefreshUrl' => route('bus_location.latest'),
                'checkedInByBus' => collect(),
            ]);
        }

        $buses = $driver->buses()
            ->with(['school'])
            ->orderBy('bus_number')
            ->get();

        $routeIds = $driver->routes()->pluck('driver_route.route_id');
        $studentsCount = \App\Models\Student::whereIn('route_id', $routeIds)->count();
        $buses->each(fn ($bus) => $bus->setAttribute('students_count', $studentsCount));

        $busIds = $buses->pluck('id');

        $fleetMap = $this->fleetMap->forSchool(null, $busIds);

        $fleetMapRefreshUrl = route('bus_location.latest');

        $activeTrips = Trip::whereIn('bus_id', $busIds)
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->get();

        $routeIdToBusId = $activeTrips->pluck('bus_id', 'route_id')->toArray();

        $checkedInByBus = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('route_id', array_keys($routeIdToBusId))
            ->whereNotNull('check_in_at')
            ->get()
            ->groupBy(fn ($record) => $routeIdToBusId[$record->route_id] ?? null)
            ->map(fn ($group) => $group->pluck('student_id')->unique()->count());

        return view('driverDashboard', compact(
            'user',
            'driver',
            'buses',
            'fleetMap',
            'fleetMapRefreshUrl',
            'checkedInByBus',
        ));
    }

    /**
     * Show the dedicated live tracking page for the driver's assigned bus(es).
     */
    public function liveTracking()
    {
        $user = Auth::user();

        $driver = Driver::where('user_id', $user->id)->first();

        if (! $driver) {
            return view('driver_live_tracking', [
                'user' => $user,
                'driver' => null,
                'buses' => collect(),
                'fleetMap' => $this->fleetMap->forSchool(null, collect()),
                'fleetMapRefreshUrl' => route('bus_location.latest'),
            ]);
        }

        $buses = $driver->buses()
            ->with(['school'])
            ->orderBy('bus_number')
            ->get();

        $busIds = $buses->pluck('id');

        $fleetMap = $this->fleetMap->forSchool(null, $busIds);

        return view('driver_live_tracking', [
            'user' => $user,
            'driver' => $driver,
            'buses' => $buses,
            'fleetMap' => $fleetMap,
            'fleetMapRefreshUrl' => route('bus_location.latest'),
        ]);
    }
}
