<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $driver = $user->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $buses = $driver->buses()
            ->with(['school', 'activeTrip'])
            ->get();

        $buses->each(function ($bus) {
            $bus->setAttribute('is_in_trip', $bus->activeTrip !== null);
        });

        return response()->json([
            'data' => [

                'id' => $driver->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $driver->phone,
                'school' => [
                    'id' => $driver->school->id,
                    'name' => $driver->school->name,
                    'address' => $driver->school->address,
                ],
                'buses' => $buses,
            ],
        ]);
    }
}
