<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Student;
use App\Models\SchoolAdmin;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\TripEndedNotification;
use App\Notifications\TripStartedNotification;
use App\Services\BusTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverTripWebController extends Controller
{
    public function __construct(private readonly BusTrackingService $gps) {}

    public function index(Request $request)
    {
        $driver = Auth::user()->driver;

        $trips = $driver->trips()
            ->with(['bus', 'route', 'school'])
            ->orderByDesc('started_at')
            ->paginate(20);

        return view('driver.trips.index', compact('trips'));
    }

    public function create(Request $request)
    {
        $driver = Auth::user()->driver;

        $activeTrip = $driver->trips()
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->with(['bus', 'route'])
            ->first();

        if ($activeTrip) {
            return redirect()->route('driver.trips.index')
                ->with('warning', 'You already have an active trip. End it first.');
        }

        $buses = $driver->buses()
            ->where('status', 'Active')
            ->orderBy('bus_number')
            ->get();

        $routes = $driver->routes()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $prefillGps = null;
        if ($buses->isNotEmpty()) {
            $prefillGps = $this->gps->getLastKnownLocation($buses->first()->id);
        }

        return view('driver.trips.create', compact('buses', 'routes', 'prefillGps'));
    }

    public function toggle(Request $request)
    {
        $driver = Auth::user()->driver;

        $validated = $request->validate([
            'bus_id'    => ['required', 'integer', 'exists:buses,id'],
            'route_id'  => ['required', 'integer', 'exists:routes,id'],
            'trip_type' => ['nullable', 'string', 'in:home_to_school,school_to_home'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ], [
            'bus_id.required'   => 'Please select a bus.',
            'bus_id.exists'     => 'Selected bus not found.',
            'route_id.required' => 'Please select a route.',
            'route_id.exists'   => 'Selected route not found.',
            'trip_type.in'      => 'Invalid trip type.',
            'latitude.numeric'  => 'Invalid latitude.',
            'longitude.numeric' => 'Invalid longitude.',
        ]);

        $bus = $driver->buses()->find($validated['bus_id']);
        if (! $bus) {
            return back()->withErrors(['bus_id' => 'You are not assigned to this bus.'])->withInput();
        }

        $route = $driver->routes()->find($validated['route_id']);
        if (! $route) {
            return back()->withErrors(['route_id' => 'You are not assigned to this route.'])->withInput();
        }

        if ($bus->status !== 'Active') {
            return back()->withErrors(['bus_id' => 'This bus is not active.'])->withInput();
        }
        if (! $route->is_active) {
            return back()->withErrors(['route_id' => 'This route is inactive.'])->withInput();
        }

        $todayTrips = $bus->trips()
            ->whereDate('started_at', now()->toDateString())
            ->orderBy('started_at')
            ->get();
        $lastTrip = $todayTrips->last();

        $isStarting = ! $lastTrip || $lastTrip->isCompleted();

        $trip = DB::transaction(function () use ($bus, $driver, $route, $validated, $lastTrip, $isStarting) {
            if ($isStarting) {
                $tripType = $validated['trip_type'] ?? Trip::TYPE_HOME_TO_SCHOOL;

                return Trip::create([
                    'bus_id'          => $bus->id,
                    'driver_id'       => $driver->id,
                    'route_id'        => $route->id,
                    'school_id'       => $bus->school_id,
                    'trip_type'       => $tripType,
                    'status'          => Trip::STATUS_IN_PROGRESS,
                    'started_at'      => now(),
                    'start_latitude'  => $validated['latitude'] ?? null,
                    'start_longitude' => $validated['longitude'] ?? null,
                    'notes'           => $validated['notes'] ?? null,
                ]);
            } else {
                $lastTrip->update([
                    'status'          => Trip::STATUS_COMPLETED,
                    'ended_at'        => now(),
                    'end_latitude'    => $validated['latitude'] ?? null,
                    'end_longitude'   => $validated['longitude'] ?? null,
                ]);
                return $lastTrip->fresh(['bus', 'route', 'school']);
            }
        });

        if ($isStarting) {
            $this->notifyTripStarted($trip);
        } else {
            $this->notifyTripEnded($trip);
        }

        $message = $isStarting
            ? "Trip started ({$trip->trip_type_label}). Parents have been notified."
            : "Trip ended ({$trip->trip_type_label}). Parents have been notified.";

        return redirect()->route('driver.trips.index')
            ->with('success', $message);
    }

    private function notifyTripEnded(Trip $trip): void
    {
        $notification = new TripEndedNotification($trip);

        $students = Student::where('route_id', $trip->route_id)
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            if ($parent = $student->parent?->user) {
                $parent->notify($notification);
            }
        }

        if ($driverUser = $trip->driver?->user) {
            $driverUser->notify($notification);
        }

        foreach (SchoolAdmin::where('school_id', $trip->school_id)->with('user')->get() as $admin) {
            if ($admin->user) {
                $admin->user->notify($notification);
            }
        }

        foreach (User::role('Super Admin')->get() as $superAdmin) {
            $superAdmin->notify($notification);
        }
    }

    private function notifyTripStarted(Trip $trip): void
    {
        $notification = new TripStartedNotification($trip);

        $students = Student::where('route_id', $trip->route_id)
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            if ($parent = $student->parent?->user) {
                $parent->notify($notification);
            }
        }

        if ($driverUser = $trip->driver?->user) {
            $driverUser->notify($notification);
        }

        foreach (SchoolAdmin::where('school_id', $trip->school_id)->with('user')->get() as $admin) {
            if ($admin->user) {
                $admin->user->notify($notification);
            }
        }

        foreach (User::role('Super Admin')->get() as $superAdmin) {
            $superAdmin->notify($notification);
        }
    }
}