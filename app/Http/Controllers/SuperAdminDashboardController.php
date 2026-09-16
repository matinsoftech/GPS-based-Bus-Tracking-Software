<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\Trip;
use App\Notifications\TripEndedNotification;
use App\Services\FleetMapService;
use App\Traits\NotifiesRouteParticipants;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    use NotifiesRouteParticipants;

    public function __construct(private readonly FleetMapService $fleetMap) {}

    /**
     * Show the super admin dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $totalSchools = School::count();
        $totalBuses = Bus::count();
        $activeBuses = Bus::where('status', 'Active')->count();
        $maintenanceBuses = Bus::where('status', 'Maintenance')->count();
        $inactiveBuses = Bus::where('status', 'Inactive')->count();
        $totalDrivers = Driver::count();
        $activeDrivers = Driver::where('status', 'Active')->count();
        $totalStudents = Student::count();
        $activeStudents = Student::where('is_active', true)->count();
        $totalRoutes = Route::count();
        $activeRoutes = Route::where('is_active', true)->count();
        $totalRouteStops = RouteStop::count();
        $totalParents = ParentProfile::count();
        $totalSchoolAdmins = SchoolAdmin::count();

        $fleetMap = $this->fleetMap->forSchool(null);

        $onlineBuses = collect($fleetMap['buses'])->where('is_online', true)->count();

        $todayAttendance = Attendance::whereDate('date', today())->count();
        $todayCheckedIn = Attendance::whereDate('date', today())
            ->whereNotNull('check_in_at')
            ->count();

        $fleet = Bus::with(['drivers', 'school'])->latest()->limit(5)->get();

        $latestRoutes = Route::with(['activeTrip.bus.drivers', 'stops'])->latest()->limit(5)->get();

        $expiringBuses = Bus::whereNotNull('insurance_expiry_date')
            ->get()
            ->filter(fn ($bus) => $bus->insurance_expiry_date
                && $bus->insurance_expiry_date->greaterThanOrEqualTo(now()->startOfDay())
                && $bus->insurance_expiry_date->lessThanOrEqualTo(now()->addMonths(2)))
            ->sortBy('insurance_expiry_date')
            ->take(4);

        $suspendedDrivers = Driver::where('status', 'Suspended')->limit(4)->get();

        $unassignedBuses = Bus::whereDoesntHave('drivers')->latest()->limit(4)->get();

        $busCountsBySchool = Bus::selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->pluck('total', 'school_id');

        $studentCountsBySchool = Student::selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->pluck('total', 'school_id');

        $routeCountsBySchool = Route::selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->pluck('total', 'school_id');

        $schools = School::orderBy('name')->get()->map(fn ($school) => [
            'school' => $school,
            'buses' => $busCountsBySchool[$school->id] ?? 0,
            'students' => $studentCountsBySchool[$school->id] ?? 0,
            'routes' => $routeCountsBySchool[$school->id] ?? 0,
        ]);

        $driverQuery = Driver::with(['school', 'buses', 'routes']);

        $search = $request->query('search');
        if ($search) {
            $driverQuery->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $statusFilter = $request->query('status');
        if ($statusFilter) {
            $driverQuery->where('status', $statusFilter);
        }

        $drivers = $driverQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('dashboard', compact(
            'user',
            'totalSchools',
            'totalBuses',
            'activeBuses',
            'maintenanceBuses',
            'inactiveBuses',
            'totalDrivers',
            'activeDrivers',
            'totalStudents',
            'activeStudents',
            'totalRoutes',
            'activeRoutes',
            'totalRouteStops',
            'totalParents',
            'totalSchoolAdmins',
            'onlineBuses',
            'todayAttendance',
            'todayCheckedIn',
            'fleet',
            'latestRoutes',
            'expiringBuses',
            'suspendedDrivers',
            'unassignedBuses',
            'schools',
            'fleetMap',
            'drivers',
        ));
    }

    /**
     * JSON payload of the live fleet map used by the dashboard auto-refresh.
     */
    public function fleetData()
    {
        return response()->json($this->fleetMap->forSchool(null));
    }

    /**
     * Show trip history across all schools with filters.
     */
    public function trips(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'bus_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'route_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:in_progress,completed'],
        ]);

        $query = Trip::with(['bus', 'route', 'driver', 'school'])
            ->orderByDesc('started_at');

        if (filled($validated['school_id'] ?? null)) {
            $query->where('school_id', $validated['school_id']);
        }

        $query
            ->when(filled($validated['search'] ?? null), fn ($q) => $this->applyTripSearch($q, $validated['search']))
            ->when(filled($validated['bus_id'] ?? null), fn ($q) => $q->where('bus_id', $validated['bus_id']))
            ->when(filled($validated['driver_id'] ?? null), fn ($q) => $q->where('driver_id', $validated['driver_id']))
            ->when(filled($validated['route_id'] ?? null), fn ($q) => $q->where('route_id', $validated['route_id']))
            ->when(filled($validated['status'] ?? null), fn ($q) => $q->where('status', $validated['status']))
            ->when(filled($validated['from'] ?? null), fn ($q) => $q->whereDate('started_at', '>=', Carbon::parse($validated['from'])->startOfDay()))
            ->when(filled($validated['to'] ?? null), fn ($q) => $q->whereDate('started_at', '<=', Carbon::parse($validated['to'])->endOfDay()));

        $trips = $query->paginate(10)->withQueryString();

        $schoolId = $request->integer('school_id') ?: null;

        $schools = School::orderBy('name')->get();
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

        return view('super-admin.trips.index', compact(
            'trips',
            'schools',
            'buses',
            'drivers',
            'routes',
            'schoolId',
        ));
    }

    /**
     * End an in-progress trip from the super admin trip list.
     */
    public function endTrip(Trip $trip)
    {
        if (! $trip->isInProgress()) {
            return redirect()->route('trips.index')
                ->with('warning', 'That trip is no longer active.');
        }

        $trip = DB::transaction(function () use ($trip) {
            $trip->update([
                'status' => Trip::STATUS_COMPLETED,
                'ended_at' => now(),
            ]);

            return $trip->fresh(['bus', 'route', 'school']);
        });

        $notification = new TripEndedNotification($trip);

        $this->notifyRouteParticipants($trip, $notification);

        $admins = SchoolAdmin::where('school_id', $trip->school_id)
            ->with('user')
            ->get();

        foreach ($admins as $admin) {
            if ($admin->user) {
                $admin->user->notify($notification);
            }
        }

        return redirect()->route('trips.index')
            ->with('success', "Trip ended ({$trip->trip_type_label}). Parents have been notified.");
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
}
