<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\FleetMapService;
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
                'children' => collect(),
                'locationsByBus' => collect(),
                'attendanceByStudent' => collect(),
            ]);
        }

        $children = $parent->children()
            ->with(['route.school', 'route.activeTrip.bus.gpsDevice', 'route.activeTrip.driver'])
            ->get();

        // Resolve active trip for each child and attach as attribute
        $children->each(function ($child) {
            $child->setAttribute('activeTrip', $child->route?->activeTrip);
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
            ->with(['route', 'route.activeTrip.driver'])
            ->get();

        return view('parents.children', compact('user', 'children'));
    }

    /**
     * Show the attendance history for a single one of the parent's children.
     *
     * A parent can only ever see the attendance of their own linked children.
     */
    public function studentAttendance(Student $student)
    {
        $user = Auth::user();

        $parent = ParentProfile::where('user_id', $user->id)->first();

        if (! $parent || $parent->children()->whereKey($student->id)->doesntExist()) {
            abort(403, 'You are not authorized to view this student\'s attendance.');
        }

        $student->load(['school', 'route']);

        $records = Attendance::query()
            ->with(['route', 'markedBy'])
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $totalRecords = $records->count();

        return view('parents.student-attendance', compact('student', 'records', 'totalRecords'));
    }
}
