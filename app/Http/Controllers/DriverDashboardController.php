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
            ->with(['routes', 'school', 'students'])
            ->withCount('students')
            ->orderBy('bus_number')
            ->get();

        $busIds = $buses->pluck('id');

        // Live positions come straight from the GPS provider, matched to each
        // bus by its IMEI (gps_device_id).
        $fleetMap = $this->fleetMap->forSchool(null, $busIds);

        $fleetMapRefreshUrl = route('bus_location.latest');

        $checkedInByBus = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->whereIn('bus_id', $busIds)
            ->whereNotNull('check_in_at')
            ->get()
            ->groupBy('bus_id')
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
