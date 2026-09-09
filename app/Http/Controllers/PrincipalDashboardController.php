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
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrincipalDashboardController extends Controller
{
    public function __construct(private readonly FleetMapService $fleetMap) {}

    /**
     * Show the principal (school admin) dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $schoolId = $this->resolveSchoolId($user);

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

        return response()->json($this->fleetMap->forSchool($this->resolveSchoolId($user)));
    }

    /**
     * Show trip history for the school.
     */
    public function tripsIndex(Request $request)
    {
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

        $query = Trip::with(['bus', 'route', 'driver', 'school'])
            ->orderByDesc('started_at');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $trips = $query->paginate(20)->withQueryString();

        return view('principal.trips.index', compact('trips'));
    }

    /**
     * End an in-progress trip from the school's trip list.
     *
     * A school admin can only end trips belonging to their own school.
     */
    public function endTrip(Trip $trip)
    {
        $user = Auth::user();
        $schoolId = $this->resolveSchoolId($user);

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
        $students = Student::whereHas('routes', fn ($query) => $query->where('route_id', $trip->route_id))
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            if ($parent = $student->parent?->user) {
                $parent->notify($notification);
            }
            if ($studentUser = $student->user) {
                $studentUser->notify($notification);
            }
        }

        $admins = SchoolAdmin::where('school_id', $trip->school_id)
            ->with('user')
            ->get();

        foreach ($admins as $admin) {
            if ($admin->user) {
                $admin->user->notify($notification);
            }
        }
    }

    /**
     * Resolve the principal's school id from the user record and fallbacks.
     */
    private function resolveSchoolId($user): ?int
    {
        $schoolId = $user->school_id;

        if (! $schoolId) {
            $schoolId = SchoolAdmin::where('user_id', $user->id)->value('school_id');
        }

        if (! $schoolId) {
            $schoolId = School::where('principal_name', $user->name)
                ->orWhere('email', $user->email)
                ->value('id');
        }

        return $schoolId;
    }
}
