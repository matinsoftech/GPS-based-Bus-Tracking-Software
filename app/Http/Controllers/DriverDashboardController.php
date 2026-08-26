<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Driver;
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
            ->with(['routes.students', 'school'])
            ->orderBy('bus_number')
            ->get();

        $buses->each(fn ($bus) => $bus->setAttribute(
            'students_count',
            $bus->routes->sum(fn ($route) => $route->students->count()),
        ));

        $busIds = $buses->pluck('id');
        $routeIds = $buses->flatMap(fn ($bus) => $bus->routes->pluck('id'));

        // Live positions come straight from the GPS provider, matched to each
        // bus by its IMEI (gps_device_id).
        $fleetMap = $this->fleetMap->forSchool(null, $busIds);

        $fleetMapRefreshUrl = route('bus_location.latest');

        // Build a map of route_id -> bus_id so we can group attendance back to buses
        $routeIdToBusId = [];
        foreach ($buses as $bus) {
            foreach ($bus->routes as $route) {
                $routeIdToBusId[$route->id] = $bus->id;
            }
        }

        $checkedInByBus = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('route_id', $routeIds)
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
            ->with(['routes', 'school'])
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
