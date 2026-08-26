<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Student;
use Illuminate\Http\Request;

class DriverBusController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $driver = $user->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.'
            ], 404);
        }

        $buses = $driver->buses()
            ->with('school')
            ->get();

        return response()->json([
            'message' => 'Driver buses data.',
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'buses' => $buses,
            ],
        ]);
    }

    public function show(Request $request, Bus $bus)
    {
        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.'
            ], 404);
        }

        $hasAccess = $driver->buses()
            ->where('buses.id', $bus->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this bus.'
            ], 403);
        }

        $bus->load([
            'school',
            'students'
        ]);

        return response()->json([
            'bus' => $bus,
        ]);
    }

    public function students(Request $request, Bus $bus)
    {
        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.'
            ], 404);
        }

        $hasAccess = $driver->buses()
            ->where('buses.id', $bus->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this bus.'
            ], 403);
        }

        $routeIds = $bus->routes()->pluck('routes.id');

        $students = Student::whereIn('route_id', $routeIds)
            ->with('parent.user')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'students' => $students,
        ]);
    }
}
