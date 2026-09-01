<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Services\NazarTrackService;
use Illuminate\Http\Request;

class DriverLiveTrackingController extends Controller
{
    public function __construct(private readonly NazarTrackService $gps) {}

    public function index(Request $request)
    {
        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.'
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['nullable', 'integer'],
        ]);

        $busIds = $driver->buses()->pluck('id');

        if (!empty($validated['bus_id'])) {
            $hasAccess = $driver->buses()
                ->whereKey($validated['bus_id'])
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Bus not found for this driver.'
                ], 404);
            }

            $busIds = collect([(int) $validated['bus_id']]);
        }

        $buses = $driver->buses()
            ->with('gpsDevice')
            ->whereIn('buses.id', $busIds)
            ->get();

        return response()->json([
            'message' => 'Driver live tracking data.',
            'data' => [
                'buses' => $buses->map(fn (Bus $bus) => [
                    'id' => $bus->id,
                    'bus_number' => $bus->bus_number,
                    'registration_number' => $bus->registration_number,
                    'capacity' => $bus->capacity,
                    'live_location' => $this->liveLocationFor($bus),
                ])->values(),
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
