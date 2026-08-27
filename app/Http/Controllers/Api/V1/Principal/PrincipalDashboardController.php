<?php

namespace App\Http\Controllers\Api\V1\Principal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Services\FleetMapService;
use Illuminate\Http\Request;

class PrincipalDashboardController extends Controller
{
    public function __construct(private readonly FleetMapService $fleetMap) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $schoolId = $this->resolveSchoolId($user);

        if (! $schoolId) {
            return response()->json([
                'message' => 'Principal profile not found.',
            ], 404);
        }

        $busQuery = Bus::query()->where('school_id', $schoolId);
        $driverQuery = Driver::query()->where('school_id', $schoolId);
        $studentQuery = Student::query()->where('school_id', $schoolId);
        $routeQuery = Route::query()->where('school_id', $schoolId);
        $attendanceQuery = Attendance::query()
            ->whereDate('date', today())
            ->whereHas('route', fn ($q) => $q->where('school_id', $schoolId));

        return response()->json([
            'message' => 'Principal dashboard data.',
            'data' => [
                'principal' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first(),
                    'status' => $user->status,
                ],
                'school' => School::find($schoolId)?->only(['id', 'name', 'code', 'address', 'phone', 'principal_name', 'status']),
                'stats' => [
                    'total_buses' => $busQuery->count(),
                    'active_buses' => (clone $busQuery)->where('status', 'Active')->count(),
                    'maintenance_buses' => (clone $busQuery)->where('status', 'Maintenance')->count(),
                    'inactive_buses' => (clone $busQuery)->where('status', 'Inactive')->count(),
                    'total_drivers' => $driverQuery->count(),
                    'active_drivers' => (clone $driverQuery)->where('status', 'Active')->count(),
                    'suspended_drivers' => (clone $driverQuery)->where('status', 'Suspended')->count(),
                    'total_students' => $studentQuery->count(),
                    'active_students' => (clone $studentQuery)->where('is_active', true)->count(),
                    'total_routes' => $routeQuery->count(),
                    'active_routes' => (clone $routeQuery)->where('is_active', true)->count(),
                    'today_attendance' => [
                        'total' => (clone $attendanceQuery)->count(),
                        'checked_in' => (clone $attendanceQuery)->whereNotNull('check_in_at')->count(),
                    ],
                ],
                'live_fleet' => $this->fleetMap->forSchool($schoolId),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        $admin = SchoolAdmin::where('user_id', $user->id)->first();

        if (! $admin) {
            return response()->json([
                'message' => 'Principal profile not found.',
            ], 404);
        }

        $admin->load('school');

        return response()->json([
            'message' => 'Principal profile data.',
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name ?: $user->name,
                'email' => $user->email,
                'phone' => $admin->phone,
                'designation' => $admin->designation,
                'address' => $admin->address,
                'role' => $user->getRoleNames()->first(),
                'status' => $user->status,
                'school' => $admin->school ? [
                    'id' => $admin->school->id,
                    'name' => $admin->school->name,
                    'address' => $admin->school->address,
                ] : null,
            ],
        ]);
    }

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
