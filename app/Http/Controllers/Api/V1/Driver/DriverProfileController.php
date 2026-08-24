<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DriverProfileController extends Controller
{
    public function show(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Driver profile data.',
            'data' => $this->profileData($driver),
        ]);
    }

    public function update(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($validated, $driver) {
            $driver->user->update(['name' => $validated['name']]);

            $driver->update(array_merge(
                Driver::nameParts($validated['name']),
                [
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                ],
            ));
        });

        return response()->json([
            'message' => 'Driver profile updated.',
            'data' => $this->profileData($driver),
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (
            $driver->profile_photo &&
            Storage::disk('public')->exists($driver->profile_photo)
        ) {
            Storage::disk('public')->delete($driver->profile_photo);
        }

        $driver->profile_photo = $request
            ->file('profile_photo')
            ->store('drivers', 'public');
        $driver->save();

        return response()->json([
            'message' => 'Profile photo updated.',
            'data' => [
                'photo_url' => asset('storage/'.$driver->profile_photo),
            ],
        ]);
    }

    private function profileData(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'name' => $driver->user->name,
            'email' => $driver->user->email,
            'photo_url' => $driver->profile_photo
                ? asset('storage/'.$driver->profile_photo)
                : null,
            'phone' => $driver->phone,
            'license_number' => $driver->license_number,
            'address' => $driver->address,
            'role' => $driver->user->getRoleNames()->first(),
            'status' => $driver->user->status,
            'school' => [
                'id' => $driver->school->id,
                'name' => $driver->school->name,
                'address' => $driver->school->address,
            ],
        ];
    }
}
