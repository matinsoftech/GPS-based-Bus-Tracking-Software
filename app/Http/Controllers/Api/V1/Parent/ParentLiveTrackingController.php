<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Route;
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
            ->with(['routes.activeTrip.bus.gpsDevice', 'routes.activeTrip.driver'])
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        $routesMap = collect();

        foreach ($children as $child) {
            foreach ($child->routes as $route) {
                if (! $routesMap->has($route->id)) {
                    $routesMap->put($route->id, [
                        'route' => $route,
                        'children' => collect(),
                    ]);
                }
                $routesMap[$route->id]['children']->push($child);
            }
        }

        $routes = $routesMap->map(fn ($entry) => $this->routeResponse(
            $entry['route'],
            $entry['children'],
        ))->values();

        return response()->json([
            'message' => 'Parent live tracking data.',
            'data' => [
                'routes_count' => $routes->count(),
                'routes' => $routes,
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

        $student->load('routes.activeTrip.bus.gpsDevice', 'routes.activeTrip.driver');

        $routes = $student->routes->map(fn ($route) => $this->routeResponse($route))->values();

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
                'routes' => $routes,
            ],
        ]);
    }

    private function routeResponse(Route $route, ?iterable $children = null): array
    {
        $bus = $route->activeTrip?->bus;
        $location = $this->liveLocationFor($bus);

        return [
            'id' => $route->id,
            'name' => $route->name,
            'route_code' => $route->route_code,
            'route_type' => $route->route_type,
            'route_type_label' => $route->route_type_label,
            'is_active' => $route->is_active,
            'children' => $children
                ? collect($children)->map(fn (Student $child) => [
                    'id' => $child->id,
                    'full_name' => $child->full_name,
                    'grade' => $child->grade,
                    'section' => $child->section,
                    'photo' => $child->photo ? asset('storage/'.$child->photo) : null,
                ])->values()
                : null,
            'live_location' => $bus ? [
                'bus' => [
                    'id' => $bus->id,
                    'bus_number' => $bus->bus_number,
                    'registration_number' => $bus->registration_number,
                ],
                'driver' => $route->activeTrip?->driver ? [
                    'id' => $route->activeTrip->driver->id,
                    'full_name' => $route->activeTrip->driver->full_name,
                    'phone' => $route->activeTrip->driver->phone,
                ] : null,
                'data' => $location,
            ] : null,
        ];
    }

    private function liveLocationFor(?Bus $bus): ?array
    {
        if (! $bus) {
            return null;
        }

        return $this->gps->locationPayload($bus)
            ?? $this->gps->lastKnownPayload($bus);
    }
}
