<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Student;
use App\Services\NazarTrackService;
use Illuminate\Http\Request;

class ParentLiveTrackingController extends Controller
{
    public function __construct(private readonly NazarTrackService $gps) {}

    public function index(Request $request)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        $children = $parent->children()
            ->with(['route.activeTrip.bus.gpsDevice'])
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        return response()->json([
            'message' => 'Parent live tracking data.',
            'data' => [
                'children_count' => $children->count(),
                'children' => $children->map(fn ($student) => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                    'route' => $student->route ? [
                        'id' => $student->route->id,
                        'name' => $student->route->name,
                        'route_code' => $student->route->route_code,
                    ] : null,
                    'live_location' => $this->liveLocationFor($student->route?->activeTrip?->bus),
                ]),
            ],
        ]);
    }

    public function show(Request $request, Student $student)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        if ($parent->children()->whereKey($student->id)->doesntExist()) {
            return response()->json([
                'message' => 'You are not authorized to view this student.',
            ], 403);
        }

        $student->load('route.activeTrip.bus.gpsDevice');

        return response()->json([
            'message' => 'Parent child live tracking data.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                ],
                'route' => $student->route ? [
                    'id' => $student->route->id,
                    'name' => $student->route->name,
                    'route_code' => $student->route->route_code,
                ] : null,
                'live_location' => $this->liveLocationFor($student->route?->activeTrip?->bus),
            ],
        ]);
    }

    /**
     * Resolve a bus's live location, falling back to its last known stored
     * position when the GPS provider has no live fix.
     */
    private function liveLocationFor(?Bus $bus): ?array
    {
        if (! $bus) {
            return null;
        }

        return $this->gps->locationPayload($bus)
            ?? $this->gps->lastKnownPayload($bus);
    }
}
