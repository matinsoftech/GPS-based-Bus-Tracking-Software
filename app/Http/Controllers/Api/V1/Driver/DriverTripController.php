<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\TripEndedNotification;
use App\Notifications\TripStartedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverTripController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:in_progress,completed'],
            'date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $driver->trips()
            ->with(['bus', 'route', 'school'])
            ->orderByDesc('started_at');

        if (! empty($validated['bus_id'])) {
            $hasAccess = $driver->buses()
                ->whereKey($validated['bus_id'])
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'message' => 'Bus not found for this driver.',
                ], 404);
            }

            $query->where('bus_id', $validated['bus_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['date'])) {
            $query->whereDate('started_at', $validated['date']);
        }

        $trips = $query->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'message' => 'Driver trips.',
            'data' => $trips->through(fn (Trip $trip) => $this->tripResponse($trip)),
            'pagination' => [
                'current_page' => $trips->currentPage(),
                'per_page' => $trips->perPage(),
                'last_page' => $trips->lastPage(),
                'total' => $trips->total(),
                'from' => $trips->firstItem(),
                'to' => $trips->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, Trip $trip): JsonResponse
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $driver->trips()
            ->whereKey($trip->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'Trip not found for this driver.',
            ], 404);
        }

        $trip->load(['bus', 'route', 'school']);

        return response()->json([
            'message' => 'Trip details.',
            'data' => $this->tripResponse($trip),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'trip_type' => ['nullable', 'string', 'in:home_to_school,school_to_home'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $bus = $driver->buses()->find($validated['bus_id']);

        if (! $bus) {
            return response()->json([
                'message' => 'Bus not found or not assigned to you.',
            ], 404);
        }

        $route = $driver->routes()->find($validated['route_id']);

        if (! $route) {
            return response()->json([
                'message' => 'Route not found or not assigned to you.',
            ], 404);
        }

        if ($bus->status !== 'Active') {
            return response()->json([
                'message' => 'Cannot operate trip on a bus that is not active.',
            ], 422);
        }

        if (! $route->is_active) {
            return response()->json([
                'message' => 'Cannot start trip on an inactive route.',
            ], 422);
        }

        $busActive = Trip::where('bus_id', $bus->id)
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->exists();

        if ($busActive) {
            return response()->json([
                'message' => 'This bus already has an active trip started by another driver.',
            ], 422);
        }

        $routeActive = Trip::where('route_id', $route->id)
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->exists();

        if ($routeActive) {
            return response()->json([
                'message' => 'This route already has an active trip started by another driver.',
            ], 422);
        }

        $driverActive = $driver->trips()
            ->where('status', Trip::STATUS_IN_PROGRESS)
            ->exists();

        if ($driverActive) {
            return response()->json([
                'message' => 'You already have an active trip. End it first.',
            ], 422);
        }

        $tripType = $validated['trip_type'] ?? Trip::tripTypeByTime();

        $trip = DB::transaction(function () use ($bus, $driver, $validated, $tripType) {
            return Trip::create([
                'bus_id' => $bus->id,
                'driver_id' => $driver->id,
                'route_id' => $validated['route_id'],
                'school_id' => $bus->school_id,
                'trip_type' => $tripType,
                'status' => Trip::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'start_latitude' => $validated['latitude'] ?? null,
                'start_longitude' => $validated['longitude'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $trip->load(['bus', 'route', 'school']);

        $this->notifyTripStarted($trip);

        return response()->json([
            'message' => 'Trip started.',
            'action' => 'started',
            'data' => $this->tripResponse($trip),
        ], 201);
    }

    public function end(Request $request, Trip $trip): JsonResponse
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $hasAccess = $driver->trips()
            ->whereKey($trip->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'Trip not found for this driver.',
            ], 404);
        }

        if (! $trip->isInProgress()) {
            return response()->json([
                'message' => 'Trip is not in progress.',
            ], 422);
        }

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $trip = DB::transaction(function () use ($trip, $validated) {
            $trip->update([
                'status' => Trip::STATUS_COMPLETED,
                'ended_at' => now(),
                'end_latitude' => $validated['latitude'] ?? null,
                'end_longitude' => $validated['longitude'] ?? null,
            ]);

            return $trip->fresh(['bus', 'route', 'school']);
        });

        $this->notifyTripEnded($trip);

        return response()->json([
            'message' => 'Trip ended.',
            'action' => 'ended',
            'data' => $this->tripResponse($trip),
        ]);
    }

    private function notifyTripStarted(Trip $trip): void
    {
        $notification = new TripStartedNotification($trip);

        $students = Student::where('route_id', $trip->route_id)
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            $parent = $student->parent?->user;

            if (! $parent) {
                continue;
            }

            $parent->notify($notification);
        }

        $driverUser = $trip->driver?->user;

        if ($driverUser) {
            $driverUser->notify($notification);
        }

        $schoolAdmins = SchoolAdmin::where('school_id', $trip->school_id)
            ->with('user')
            ->get();

        foreach ($schoolAdmins as $admin) {
            if (! $admin->user) {
                continue;
            }

            $admin->user->notify($notification);
        }

        $superAdmins = User::role('Super Admin')->get();

        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify($notification);
        }
    }

    private function notifyTripEnded(Trip $trip): void
    {
        $notification = new TripEndedNotification($trip);

        $students = Student::where('route_id', $trip->route_id)
            ->with('parent.user')
            ->get();

        foreach ($students as $student) {
            $parent = $student->parent?->user;

            if (! $parent) {
                continue;
            }

            $parent->notify($notification);
        }

        $driverUser = $trip->driver?->user;

        if ($driverUser) {
            $driverUser->notify($notification);
        }

        $schoolAdmins = SchoolAdmin::where('school_id', $trip->school_id)
            ->with('user')
            ->get();

        foreach ($schoolAdmins as $admin) {
            if (! $admin->user) {
                continue;
            }

            $admin->user->notify($notification);
        }

        $superAdmins = User::role('Super Admin')->get();

        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify($notification);
        }
    }

    private function tripResponse(Trip $trip): array
    {
        return [
            'id' => $trip->id,
            'bus' => [
                'id' => $trip->bus->id,
                'bus_number' => $trip->bus->bus_number,
                'registration_number' => $trip->bus->registration_number,
            ],
            'route' => $trip->route ? [
                'id' => $trip->route->id,
                'name' => $trip->route->name,
                'route_code' => $trip->route->route_code,
            ] : null,
            'school' => [
                'id' => $trip->school->id,
                'name' => $trip->school->name,
            ],
            'trip_type' => $trip->trip_type,
            'trip_type_label' => Trip::types()[$trip->trip_type] ?? $trip->trip_type,
            'status' => $trip->status,
            'status_label' => Trip::statuses()[$trip->status] ?? $trip->status,
            'started_at' => $trip->started_at?->toIso8601String(),
            'ended_at' => $trip->ended_at?->toIso8601String(),
            'duration' => $trip->isCompleted() ? $trip->duration() : null,
            'duration_minutes' => $trip->isCompleted() ? $trip->durationInMinutes() : null,
            'start_location' => $trip->start_latitude
                ? ['latitude' => $trip->start_latitude, 'longitude' => $trip->start_longitude]
                : null,
            'end_location' => $trip->end_latitude
                ? ['latitude' => $trip->end_latitude, 'longitude' => $trip->end_longitude]
                : null,
            'notes' => $trip->notes,
        ];
    }
}
