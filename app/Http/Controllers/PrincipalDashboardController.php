<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\Trip;
use App\Notifications\TripEndedNotification;
use App\Services\FleetMapService;
use App\Services\SchoolContextService;
use App\Traits\NotifiesRouteParticipants;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrincipalDashboardController extends Controller
{
    use NotifiesRouteParticipants;

    public function __construct(
        private readonly FleetMapService $fleetMap,
        private readonly SchoolContextService $context
    ) {}

    /**
     * Show the principal (school admin) dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $schoolId = $this->context->resolveSchool($user)?->id;

        $busQuery = Bus::query();
        $driverQuery = Driver::query();
        $studentQuery = Student::query();
        $routeQuery = Route::query();

        if ($schoolId) {
            $busQuery->where('school_id', $schoolId);
            $driverQuery->where('school_id', $schoolId);
            $studentQuery->where('school_id', $schoolId);
            $routeQuery->where('school_id', $schoolId);
        }

        $totalBuses = (clone $busQuery)->count();
        $activeBuses = (clone $busQuery)->where('status', 'Active')->count();
        $maintenanceBuses = (clone $busQuery)->where('status', 'Maintenance')->count();
        $inactiveBuses = (clone $busQuery)->where('status', 'Inactive')->count();
        $totalDrivers = (clone $driverQuery)->count();
        $activeDrivers = (clone $driverQuery)->where('status', 'Active')->count();
        $totalStudents = (clone $studentQuery)->count();
        $activeRoutes = (clone $routeQuery)->where('is_active', true)->count();
        $totalRoutes = (clone $routeQuery)->count();

        $fleet = (clone $busQuery)->with('drivers')->latest()->limit(5)->get();

        $upcomingRoutes = (clone $routeQuery)->with('activeTrip.bus.drivers', 'stops')->latest()->limit(5)->get();

        $expiringBuses = (clone $busQuery)
            ->whereNotNull('insurance_expiry_date')
            ->get()
            ->filter(fn ($bus) => $bus->insurance_expiry_date
                && $bus->insurance_expiry_date->greaterThanOrEqualTo(now()->startOfDay())
                && $bus->insurance_expiry_date->lessThanOrEqualTo(now()->addMonths(2)))
            ->sortBy('insurance_expiry_date')
            ->take(4);

        $suspendedDrivers = (clone $driverQuery)->where('status', 'Suspended')->limit(4)->get();

        $school = $schoolId ? School::find($schoolId) : null;

        $fleetMap = $this->fleetMap->forSchool($schoolId);

        return view('principalDashboard', compact(
            'user',
            'school',
            'totalBuses',
            'activeBuses',
            'maintenanceBuses',
            'inactiveBuses',
            'totalDrivers',
            'activeDrivers',
            'totalStudents',
            'activeRoutes',
            'totalRoutes',
            'fleet',
            'upcomingRoutes',
            'expiringBuses',
            'suspendedDrivers',
            'fleetMap',
        ));
    }

    /**
     * JSON payload of the live fleet map used by the dashboard auto-refresh.
     */
    public function fleetData()
    {
        $user = Auth::user();

        return response()->json($this->fleetMap->forSchool($this->context->resolveSchool($user)?->id));
    }

    /**
     * Show trip history for the school.
     */
    public function tripsIndex(Request $request)
    {
        $user = Auth::user();
        $schoolId = $this->context->resolveSchool($user)?->id;

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'bus_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'route_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:in_progress,completed'],
        ]);

        $query = Trip::with(['bus', 'route', 'driver', 'school'])
            ->orderByDesc('started_at');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $query
            ->when(filled($validated['search'] ?? null), fn ($q) => $this->applyTripSearch($q, $validated['search']))
            ->when(filled($validated['bus_id'] ?? null), fn ($q) => $q->where('bus_id', $validated['bus_id']))
            ->when(filled($validated['driver_id'] ?? null), fn ($q) => $q->where('driver_id', $validated['driver_id']))
            ->when(filled($validated['route_id'] ?? null), fn ($q) => $q->where('route_id', $validated['route_id']))
            ->when(filled($validated['status'] ?? null), fn ($q) => $q->where('status', $validated['status']))
            ->when(filled($validated['from'] ?? null), fn ($q) => $q->whereDate('started_at', '>=', \Illuminate\Support\Carbon::parse($validated['from'])->startOfDay()))
            ->when(filled($validated['to'] ?? null), fn ($q) => $q->whereDate('started_at', '<=', \Illuminate\Support\Carbon::parse($validated['to'])->endOfDay()));

        $trips = $query->paginate(20)->withQueryString();

        $buses = Bus::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('bus_number')
            ->get();
        $drivers = Driver::with('user')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('first_name')
            ->get();
        $routes = Route::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get();

        return view('principal.trips.index', compact('trips', 'buses', 'drivers', 'routes'));
    }

    /**
     * Apply a keyword search across the trip's bus, route, driver and school.
     */
    private function applyTripSearch($query, string $term)
    {
        $needle = '%'.$term.'%';

        return $query->where(function ($query) use ($needle) {
            $query->whereHas('bus', fn ($q) => $q
                ->where('bus_number', 'like', $needle)
                ->orWhere('registration_number', 'like', $needle))
                ->orWhereHas('route', fn ($q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('route_code', 'like', $needle))
                ->orWhereHas('driver', fn ($q) => $q
                    ->where('first_name', 'like', $needle)
                    ->orWhere('last_name', 'like', $needle)
                    ->orWhere('employee_id', 'like', $needle))
                ->orWhereHas('school', fn ($q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('code', 'like', $needle));
        });
    }

    /**
     * End an in-progress trip from the school's trip list.
     *
     * A school admin can only end trips belonging to their own school.
     */
    public function endTrip(Trip $trip)
    {
        $user = Auth::user();
        $schoolId = $this->context->resolveSchool($user)?->id;

        if ($schoolId !== null && $trip->school_id !== $schoolId) {
            abort(403, 'You are not authorized to end this trip.');
        }

        if (! $trip->isInProgress()) {
            return redirect()->route('principal.trips.index')
                ->with('warning', 'That trip is no longer active.');
        }

        $trip = DB::transaction(function () use ($trip) {
            $trip->update([
                'status' => Trip::STATUS_COMPLETED,
                'ended_at' => now(),
            ]);

            return $trip->fresh(['bus', 'route', 'school']);
        });

        $this->notifyParentsAndPrincipal($trip, new TripEndedNotification($trip));

        return redirect()->route('principal.trips.index')
            ->with('success', "Trip ended ({$trip->trip_type_label}). Parents have been notified.");
    }

    /**
     * Notify the parents/students on the trip's route and the school's admins.
     */
    private function notifyParentsAndPrincipal(Trip $trip, Notification $notification): void
    {
        $this->notifyRouteParticipants($trip, $notification);

        $admins = SchoolAdmin::where('school_id', $trip->school_id)
            ->with('user')
            ->get();

        foreach ($admins as $admin) {
            if ($admin->user) {
                $admin->user->notify($notification);
            }
        }
    }
}
