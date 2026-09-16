<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Student;
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
            ->with(['activeTrip'])
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
                    'school_name' => $driver->school->name ?? null,
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

        $stops = $route->stops()
            // ->with(['students' => fn ($query) => $query->with('parent.user')->orderBy('first_name')])
            ->get();

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

    public function showStudent(Request $request, Student $student)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $student->routes()
            ->whereIn('routes.id', $driver->routes()->pluck('routes.id'))
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this student.',
            ], 403);
        }

        $student->load(['parent.user', 'routes', 'school', 'stops.route']);

        return response()->json([
            'message' => 'Student details.',
            'data' => [
                'student' => $this->studentPayload($student),
            ],
        ]);
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'admission_no' => $student->admission_no,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'gender' => $student->gender,
            'grade' => $student->grade,
            'section' => $student->section,
            'roll_no' => $student->roll_no,
            'date_of_birth' => $student->date_of_birth?->toDateString(),
            // 'pickup_location' => $student->pickup_location,
            // 'drop_location' => $student->drop_location,
            // 'pickup_latitude' => $student->pickup_latitude,
            // 'pickup_longitude' => $student->pickup_longitude,
            // 'drop_latitude' => $student->drop_latitude,
            // 'drop_longitude' => $student->drop_longitude,
            'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
            'is_active' => $student->is_active,
            'parent' => $student->parent ? [
                'id' => $student->parent->id,
                'name' => $student->parent->user?->name ?? $student->parent->name,
                'phone' => $student->parent->phone,
            ] : null,
            'routes' => $student->routes->map(fn ($route) => [
                'id' => $route->id,
                'name' => $route->name,
                'route_code' => $route->route_code,
                'is_active' => $route->is_active,
                'stops' => ($student->stops->where('route_id', $route->id))->values()->map(fn ($stop) => [
                    'id' => $stop->id,
                    'name' => $stop->name,
                    'stop_order' => $stop->stop_order,
                    'latitude' => $stop->latitude,
                    'longitude' => $stop->longitude,
                ])->values(),
            ])->values(),
            'school' => $student->school ? [
                'id' => $student->school->id,
                'name' => $student->school->name,
            ] : null,
            'stops' => $student->stops->map(fn ($stop) => [
                'id' => $stop->id,
                'name' => $stop->name,
                'stop_order' => $stop->stop_order,
                'latitude' => $stop->latitude,
                'longitude' => $stop->longitude,
                'route_id' => $stop->route_id,
                'route_name' => $stop->route?->name,
            ])->values(),
        ];
    }

    public function stopStudents(Request $request, $stopId)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $stop = RouteStop::where('id', $stopId)->first();

        if (! $stop) {
            return response()->json([
                'message' => 'Stop not found.',
            ], 404);
        }

        $hasAccess = $driver->routes()
            ->where('routes.id', $stop->route_id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You are not assigned to this route.',
            ], 403);
        }

        $students = $stop->students()
            ->with('parent.user')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'message' => 'Stop students data.',
            'data' => [
                'stop_id' => $stop->id,
                'stop_name' => $stop->name,
                'route_id' => $stop->route_id,
                'students' => $students,
            ],
        ]);
    }
}
