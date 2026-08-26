<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Trip;
use App\Services\NazarTrackService;
use Illuminate\Http\Request;

class ParentBusController extends Controller
{
    public function __construct(private readonly NazarTrackService $gps) {}

    public function show(Request $request, Student $student)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        $hasAccess = $parent->children()
            ->whereKey($student->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => "You are not authorized to view this student's route.",
            ], 403);
        }

        $route = $student->route()
            ->with(['stops', 'drivers', 'school'])
            ->first();

        if (! $route) {
            return response()->json([
                'message' => 'Route not found for this child.',
            ], 404);
        }

        $activeTrip = Trip::where('route_id', $route->id)
            ->where('status', 'in_progress')
            ->with(['bus.gpsDevice', 'driver'])
            ->first();

        return response()->json([
            'message' => 'Parent child route data.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                    'pickup_location' => $student->pickup_location,
                    'drop_location' => $student->drop_location,
                ],
                'route' => [
                    'id' => $route->id,
                    'name' => $route->name,
                    'route_code' => $route->route_code,
                    'start_location' => $route->start_location,
                    'end_location' => $route->end_location,
                    'is_active' => $route->is_active,
                    'stops' => $route->stops->map(fn ($stop) => [
                        'id' => $stop->id,
                        'name' => $stop->name,
                        'latitude' => $stop->latitude,
                        'longitude' => $stop->longitude,
                        'stop_order' => $stop->stop_order,
                        'pickup_time' => $stop->pickup_time,
                        'drop_time' => $stop->drop_time,
                    ])->values(),
                    'drivers' => $route->drivers->map(fn ($d) => [
                        'id' => $d->id,
                        'name' => $d->full_name,
                        'phone' => $d->phone,
                    ]),
                    'buses' => $activeTrip?->bus ? [[
                        'id' => $activeTrip->bus->id,
                        'bus_number' => $activeTrip->bus->bus_number,
                        'registration_number' => $activeTrip->bus->registration_number,
                        'capacity' => $activeTrip->bus->capacity,
                        'status' => $activeTrip->bus->status,
                        'gps_device_id' => $activeTrip->bus->gps_device_id,
                    ]] : [],
                    'school' => $route->school ? [
                        'id' => $route->school->id,
                        'name' => $route->school->name,
                        'address' => $route->school->address,
                    ] : null,
                ],
                'live_location' => $this->gps->locationPayload($activeTrip?->bus),
            ],
        ]);
    }
}
