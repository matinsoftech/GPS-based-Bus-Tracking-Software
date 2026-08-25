<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Trip;
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
            ->with(['bus', 'school'])
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

    public function toggle(Request $request): JsonResponse
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['required', 'integer'],
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

        if ($bus->status !== 'Active') {
            return response()->json([
                'message' => 'Cannot operate trip on a bus that is not active.',
            ], 422);
        }

        $todayTrips = $bus->trips()
            ->whereDate('started_at', now()->toDateString())
            ->orderBy('started_at')
            ->get();

        $lastTrip = $todayTrips->last();

        if ($lastTrip && $lastTrip->isCompleted() && $lastTrip->trip_type === Trip::TYPE_SCHOOL_TO_HOME) {
            return response()->json([
                'message' => 'All trips for today are completed.',
                'next_action' => 'day_complete',
            ], 422);
        }

        $action = null;
        $message = null;
        $nextAction = null;
        $trip = null;

        if (! $lastTrip) {
            $trip = DB::transaction(function () use ($bus, $driver, $validated) {
                return Trip::create([
                    'bus_id' => $bus->id,
                    'driver_id' => $driver->id,
                    'school_id' => $bus->school_id,
                    'trip_type' => Trip::TYPE_HOME_TO_SCHOOL,
                    'status' => Trip::STATUS_IN_PROGRESS,
                    'started_at' => now(),
                    'start_latitude' => $validated['latitude'] ?? null,
                    'start_longitude' => $validated['longitude'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            $action = 'started';
            $message = 'Trip started (Home to School).';
            $nextAction = 'end_trip';

        } elseif ($lastTrip->trip_type === Trip::TYPE_HOME_TO_SCHOOL && $lastTrip->isCompleted()) {
            $trip = DB::transaction(function () use ($bus, $driver, $validated) {
                return Trip::create([
                    'bus_id' => $bus->id,
                    'driver_id' => $driver->id,
                    'school_id' => $bus->school_id,
                    'trip_type' => Trip::TYPE_SCHOOL_TO_HOME,
                    'status' => Trip::STATUS_IN_PROGRESS,
                    'started_at' => now(),
                    'start_latitude' => $validated['latitude'] ?? null,
                    'start_longitude' => $validated['longitude'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            $action = 'started';
            $message = 'Trip started (School to Home).';
            $nextAction = 'end_trip';

        } elseif ($lastTrip->trip_type === Trip::TYPE_HOME_TO_SCHOOL && $lastTrip->isInProgress()) {
            $trip = DB::transaction(function () use ($lastTrip, $validated) {
                $lastTrip->update([
                    'status' => Trip::STATUS_COMPLETED,
                    'ended_at' => now(),
                    'end_latitude' => $validated['latitude'] ?? null,
                    'end_longitude' => $validated['longitude'] ?? null,
                ]);

                return $lastTrip->fresh(['bus', 'school']);
            });

            $action = 'ended';
            $message = 'Trip ended (Home to School).';
            $nextAction = 'start_next_trip';

        } elseif ($lastTrip->trip_type === Trip::TYPE_SCHOOL_TO_HOME && $lastTrip->isInProgress()) {
            $trip = DB::transaction(function () use ($lastTrip, $validated) {
                $lastTrip->update([
                    'status' => Trip::STATUS_COMPLETED,
                    'ended_at' => now(),
                    'end_latitude' => $validated['latitude'] ?? null,
                    'end_longitude' => $validated['longitude'] ?? null,
                ]);

                return $lastTrip->fresh(['bus', 'school']);
            });

            $action = 'ended';
            $message = 'Trip ended (School to Home). All trips completed for today.';
            $nextAction = 'day_complete';
        }

        if (is_null($trip->relationLoaded('bus'))) {
            $trip->load(['bus', 'school']);
        }

        return response()->json([
            'message' => $message,
            'action' => $action,
            'next_action' => $nextAction,
            'data' => $this->tripResponse($trip),
        ], $action === 'started' ? 201 : 200);
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
