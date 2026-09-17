<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\FleetMapService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function __construct(private readonly FleetMapService $fleetMap) {}

    /**
     * Show the parent dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $parent = ParentProfile::where('user_id', $user->id)->first();

        if (! $parent) {
            return view('parentDashboard', [
                'user' => $user,
                'parent' => null,
                'school' => null,
                'children' => collect(),
                'locationsByBus' => collect(),
                'attendanceByStudent' => collect(),
            ]);
        }

        $children = $parent->children()
            ->with(['routes.school', 'routes.activeTrip.bus.gpsDevice', 'routes.activeTrip.driver'])
            ->get();

        $school = $parent->school;

        // Resolve active trip for each child and attach as attribute
        $children->each(function ($child) {
            $child->setAttribute('activeTrip', $child->routes->first()?->activeTrip);
        });

        // Build locations keyed by bus_id from active trips
        $activeBuses = $children
            ->filter(fn ($child) => $child->getAttribute('activeTrip')?->bus)
            ->pluck('activeTrip.bus')
            ->unique('id');

        $locationsByBus = $this->fleetMap->latestLocationsByDevice(
            $activeBuses->pluck('id')->values(),
            ['gpsDevice']
        )->keyBy(fn ($location) => $location->gpsDevice?->bus_id);

        $attendanceByStudent = Attendance::whereIn('student_id', $children->pluck('id'))
            ->where('date', today())
            ->get()
            ->groupBy('student_id');

        return view('parentDashboard', compact(
            'user',
            'parent',
            'school',
            'children',
            'locationsByBus',
            'attendanceByStudent',
        ));
    }

    /**
     * Show the parent's children list.
     */
    public function children()
    {
        $user = Auth::user();

        $parent = ParentProfile::where('user_id', $user->id)->first();

        if (! $parent) {
            return view('parents.children', [
                'user' => $user,
                'children' => collect(),
            ]);
        }

        $children = $parent->children()
            ->with(['routes', 'routes.activeTrip.driver'])
            ->get();

        return view('parents.children', compact('user', 'children'));
    }

    /**
     * Show the attendance history for a single one of the parent's children.
     *
     * A parent can only ever see the attendance of their own linked children.
     */
    public function studentAttendance(Request $request, Student $student)
    {
        $user = Auth::user();

        $parent = ParentProfile::where('user_id', $user->id)->first();

        if (! $parent || $parent->children()->whereKey($student->id)->doesntExist()) {
            abort(403, 'You are not authorized to view this student\'s attendance.');
        }

        $student->load(['school', 'routes']);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'date' => ['nullable', 'date'],
            'period' => ['nullable', 'in:today,yesterday,this_week,this_month,all'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
        ]);

        $singleDate = ! empty($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : null;

        $period = $validated['period'] ?? '';

        [$from, $to] = match (true) {
            $singleDate !== null => [$singleDate->copy()->startOfDay(), $singleDate->copy()->endOfDay()],
            $period === 'today' => [now()->startOfDay(), now()->endOfDay()],
            $period === 'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            $period === 'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            $period === 'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            $period === 'all' => [null, null],
            default => [
                ! empty($validated['from'])
                    ? Carbon::parse($validated['from'])->startOfDay()
                    : null,
                ! empty($validated['to'])
                    ? Carbon::parse($validated['to'])->endOfDay()
                    : null,
            ],
        };

        $routeId = $validated['route_id'] ?? null;

        $records = Attendance::query()
            ->with(['route', 'markedBy'])
            ->where('student_id', $student->id)
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->when($routeId, fn ($query) => $query->where('route_id', $routeId))
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $totalRecords = $records->count();

        $routes = $student->routes;

        return view('parents.student-attendance', compact('student', 'records', 'totalRecords', 'from', 'to', 'routeId', 'routes', 'singleDate', 'period'));
    }
}
