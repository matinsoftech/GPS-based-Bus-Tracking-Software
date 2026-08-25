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
use App\Services\FleetMapService;
use Illuminate\Support\Facades\Auth;

class SuperAdminDashboardController extends Controller
{
    public function __construct(private readonly FleetMapService $fleetMap) {}

    /**
     * Show the super admin dashboard.
     */
    public function index()
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

        $fleet = Bus::with(['drivers', 'school', 'routes'])->latest()->limit(5)->get();

        $latestRoutes = Route::with(['buses.drivers', 'stops'])->latest()->limit(5)->get();

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
        ));
    }

    /**
     * JSON payload of the live fleet map used by the dashboard auto-refresh.
     */
    public function fleetData()
    {
        return response()->json($this->fleetMap->forSchool(null));
    }
}
