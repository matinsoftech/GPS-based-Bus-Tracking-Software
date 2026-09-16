<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Trip;
use App\Models\User;
use App\Services\NazarTrackService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class BusController extends Controller
{
    public function __construct(private readonly NazarTrackService $gpsService) {}

    /**
     * Display all buses.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Bus::with(['school', 'drivers']);

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        $selectedSchool = ! $this->isSchoolLevelAdmin($user) ? $request->school_id : null;

        if ($selectedSchool) {
            $query->where('school_id', $selectedSchool);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('bus_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $buses = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = ! $this->isSchoolLevelAdmin($user)
            ? School::orderBy('name')->get()
            : collect();

        return view('buses.index', compact('buses', 'schools', 'selectedSchool'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        $drivers = $this->availableDrivers($school);

        return view('buses.create', compact('school', 'schools', 'drivers'));
    }

    /**
     * Store a new bus.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = $this->validationRules();

        if (! $this->isSchoolLevelAdmin($user)) {
            $rules['school_id'] = [
                'nullable',
                'exists:schools,id',
            ];
        }

        $validated = $request->validate($rules);

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $validated['school_id'] = $schoolId;
            }
        }

        if (empty($validated['school_id'] ?? null)) {
            $schoolId = $request->input('school_id');

            if (! $schoolId) {
                $schoolId = School::query()->value('id');
            }

            if ($schoolId) {
                $validated['school_id'] = $schoolId;
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['school_id' => 'Please create a school before adding a bus.'])
                    ->withInput();
            }
        }

        if ($error = app(\App\Services\PlanLimitService::class)->assertCreatable('buses', (int) $validated['school_id'])) {
            return back()->withInput()->withErrors(['plan_limit' => $error]);
        }

        $validated['created_by'] = $user->id;
        $driverIds = $validated['driver_ids'] ?? [];
        unset($validated['driver_ids']);

        try {
            DB::transaction(function () use ($validated, $driverIds) {
                $bus = Bus::create($validated);
                $bus->drivers()->sync($driverIds);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create bus.']);
        }

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus added successfully.');
    }

    /**
     * Display a single bus.
     */
    public function show(Bus $bus)
    {
        $this->authorizeBus($bus);

        $bus->load(['school', 'creator', 'drivers', 'gpsDevice']);

        $latestLocation = $this->gpsService->locationPayload($bus)
            ?? $this->gpsService->lastKnownPayload($bus);

        $tripRecords = $bus->trips()
            ->with(['route.stops', 'driver'])
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        $busRoutes = $tripRecords
            ->pluck('route')
            ->filter()
            ->keyBy('id')
            ->values()
            ->map(fn ($route) => [
                'id' => $route->id,
                'name' => $route->name,
                'route_code' => $route->route_code,
                'start_location' => $route->start_location,
                'end_location' => $route->end_location,
                'stops' => $route->stops
                    ->map(fn ($stop) => [
                        'id' => $stop->id,
                        'name' => $stop->name,
                        'latitude' => $stop->latitude,
                        'longitude' => $stop->longitude,
                        'stop_order' => $stop->stop_order,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values();

        $tripRoutes = $tripRecords
            ->pluck('route')
            ->filter()
            ->keyBy('id')
            ->values();

        $tripDrivers = $tripRecords
            ->pluck('driver')
            ->filter()
            ->keyBy('id')
            ->values();

        return view('buses.show', compact('bus', 'latestLocation', 'busRoutes', 'tripRoutes', 'tripDrivers'));
    }

    /**
     * Show the trip history of a single bus with filters.
     */
    public function trips(Bus $bus, Request $request)
    {
        $this->authorizeBus($bus);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'route_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'trip_type' => ['nullable', Rule::in(array_keys(Trip::types()))],
            'status' => ['nullable', Rule::in(array_keys(Trip::statuses()))],
        ]);

        $trips = $bus->trips()
            ->with(['route', 'driver', 'school'])
            ->when(filled($validated['search'] ?? null), fn ($q) => $this->applyTripSearch($q, $validated['search']))
            ->when(filled($validated['route_id'] ?? null), fn ($q) => $q->where('route_id', $validated['route_id']))
            ->when(filled($validated['driver_id'] ?? null), fn ($q) => $q->where('driver_id', $validated['driver_id']))
            ->when(filled($validated['trip_type'] ?? null), fn ($q) => $q->where('trip_type', $validated['trip_type']))
            ->when(filled($validated['status'] ?? null), fn ($q) => $q->where('status', $validated['status']))
            ->when(filled($validated['from'] ?? null), fn ($q) => $q->whereDate('started_at', '>=', \Illuminate\Support\Carbon::parse($validated['from'])->startOfDay()))
            ->when(filled($validated['to'] ?? null), fn ($q) => $q->whereDate('started_at', '<=', \Illuminate\Support\Carbon::parse($validated['to'])->endOfDay()))
            ->orderByDesc('started_at')
            ->paginate(10)
            ->withQueryString();

        $routes = $bus->trips()
            ->with('route')
            ->get()
            ->pluck('route')
            ->filter()
            ->keyBy('id')
            ->values();

        $drivers = $bus->trips()
            ->with('driver')
            ->get()
            ->pluck('driver')
            ->filter()
            ->keyBy('id')
            ->values();

        return view('buses.trips', compact('bus', 'trips', 'routes', 'drivers'));
    }

    /**
     * Apply a keyword search across a trip's route and driver.
     */
    private function applyTripSearch($query, string $term)
    {
        $needle = '%'.$term.'%';

        return $query->where(function ($query) use ($needle) {
            $query->whereHas('route', fn ($q) => $q
                ->where('name', 'like', $needle)
                ->orWhere('route_code', 'like', $needle))
                ->orWhereHas('driver', fn ($q) => $q
                    ->where('first_name', 'like', $needle)
                    ->orWhere('last_name', 'like', $needle)
                    ->orWhere('employee_id', 'like', $needle))
                ->orWhereHas('school', fn ($q) => $q
                    ->where('name', 'like', $needle)
                    ->orWhere('code', 'like', $needle));
        });
    }

    /**
     * Show edit form.
     */
    public function edit(Bus $bus)
    {
        $this->authorizeBus($bus);

        $bus->load(['drivers', 'school']);

        $drivers = $this->availableDrivers(null, $bus);

        return view('buses.edit', compact('bus', 'drivers'));
    }

    /**
     * Update bus.
     */
    public function update(Request $request, Bus $bus)
    {
        $this->authorizeBus($bus);

        $rules = $this->validationRules($bus);

        $validated = $request->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | School cannot be changed through edit; keep the original assignment
        |--------------------------------------------------------------------------
        */

        $validated['school_id'] = $bus->school_id;

        $driverIds = $validated['driver_ids'] ?? [];
        unset($validated['driver_ids']);

        try {
            DB::transaction(function () use ($bus, $validated, $driverIds) {
                $bus->update($validated);
                $bus->drivers()->sync($driverIds);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update bus.']);
        }

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus updated successfully.');
    }

    /**
     * Delete bus.
     */
    public function destroy(Bus $bus)
    {
        $this->authorizeBus($bus);

        $bus->delete();

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus deleted successfully.');
    }

    /**
     * Shared validation rules.
     */
    private function validationRules(?Bus $bus = null): array
    {
        $busId = $bus?->id;

        return [
            'bus_number' => [
                'required',
                'string',
                'max:50',
                'unique:buses,bus_number'.($busId ? ",{$busId}" : ''),
            ],

            'registration_number' => [
                'required',
                'string',
                'max:50',
                'unique:buses,registration_number'.($busId ? ",{$busId}" : ''),
            ],

            'make' => 'nullable|string|max:100',

            'model' => 'nullable|string|max:100',

            'year' => 'nullable|integer|min:1950|max:'.now()->year,

            'capacity' => 'required|integer|min:1|max:200',

            'fuel_type' => [
                'nullable',
                Rule::in(['Diesel', 'Petrol', 'Electric', 'CNG', 'Hybrid']),
            ],

            'gps_device_id' => [
                'nullable',
                'string',
                'max:100',
                'unique:buses,gps_device_id'.($busId ? ",{$busId}" : ''),
            ],

            'insurance_number' => 'nullable|string|max:100',

            'insurance_expiry_date' => 'nullable|date',

            'last_service_date' => 'nullable|date',

            'status' => [
                'required',
                Rule::in(['Active', 'Maintenance', 'Inactive']),
            ],

            'notes' => 'nullable|string',

            'driver_ids' => 'nullable|array',
            'driver_ids.*' => 'exists:drivers,id',
        ];
    }

    /**
     * Drivers the current user may assign to a bus.
     */
    private function availableDrivers(?School $school, ?Bus $bus = null): Collection
    {
        $user = Auth::user();

        $query = Driver::query()->with('school');

        $schoolId = $school?->id;

        if (! $schoolId) {
            $schoolId = $this->getUserSchoolId($user);
        }

        if (! $schoolId && $bus) {
            $schoolId = $bus->school_id;
        }

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return $query->orderBy('first_name')->get();
    }

    /**
     * Make sure school-level admins can only access their own school's buses.
     */
    private function authorizeBus(Bus $bus): void
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $bus->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this bus.');
            }
        }
    }

    private function isSchoolLevelAdmin(?User $user): bool
    {
        return $user && $user->hasAnyRole(['School Admin', 'Principal']);
    }

    private function getUserSchoolId(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        if (! empty($user->school_id)) {
            return (int) $user->school_id;
        }

        $schoolAdmin = SchoolAdmin::where('user_id', $user->id)->first();

        if ($schoolAdmin && ! empty($schoolAdmin->school_id)) {
            return (int) $schoolAdmin->school_id;
        }

        $school = School::where('principal_name', $user->name)
            ->orWhere('email', $user->email)
            ->first();

        return $school?->id;
    }
}