<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DriverBusController extends Controller
{
    /**
     * Attach an is_in_trip boolean to each model so the mobile UI can
     * disable / mark buses and routes that already have an active trip.
     */
    private function addTripFlags(Collection $models): void
    {
        $models->each(function ($model) {
            $model->setAttribute('is_in_trip', $model->activeTrip !== null);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $driver = $user->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $buses = $driver->buses()
            ->with(['activeTrip'])
            ->get();

        $this->addTripFlags($buses);

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

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $driver->buses()
            ->where('buses.id', $bus->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this bus.',
            ], 403);
        }

        $bus->load(['school', 'activeTrip']);

        $bus->setAttribute('is_in_trip', $bus->activeTrip !== null);

        return response()->json([
            'bus' => $bus,
        ]);
    }

    public function routes(Request $request)
    {
        $user = $request->user();

        $driver = $user->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $routes = $driver->routes()
            ->with(['school', 'activeTrip'])
            ->orderBy('name')
            ->get();

        $this->addTripFlags($routes);

        return response()->json([
            'message' => 'Driver routes data.',
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'routes' => $routes,
            ],
        ]);
    }

    public function stops(Request $request, Route $route)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $driver->routes()
            ->whereKey($route->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this route.',
            ], 403);
        }

        $stops = $route->stops()->get();

        return response()->json([
            'message' => 'Route stops data.',
            'data' => [
                'route_id' => $route->id,
                'route_name' => $route->name,
                'stops' => $stops,
            ],
        ]);
    }

    public function students(Request $request, Route $route)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $driver->routes()
            ->whereKey($route->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this route.',
            ], 403);
        }

        $students = $route->students()
            ->with('parent.user')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'message' => 'Route students data.',
            'data' => [
                'route_id' => $route->id,
                'route_name' => $route->name,
                'students' => $students,
            ],
        ]);
    }
}
