<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
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

        $query = Bus::with(['school', 'routes', 'drivers']);

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
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

        return view('buses.index', compact('buses'));
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
        $routes = $this->availableRoutes($school);

        return view('buses.create', compact('school', 'schools', 'drivers', 'routes'));
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

        $validated['created_by'] = $user->id;
        $routeIds = $validated['route_ids'] ?? [];
        unset($validated['route_ids']);
        $driverIds = $validated['driver_ids'] ?? [];
        unset($validated['driver_ids']);

        try {
            DB::transaction(function () use ($validated, $routeIds, $driverIds) {
                $bus = Bus::create($validated);
                $bus->routes()->sync($routeIds);
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

        $bus->load(['school', 'creator', 'routes', 'drivers', 'gpsDevice']);

        $latestLocation = $this->gpsService->locationPayload($bus);

        return view('buses.show', compact('bus', 'latestLocation'));
    }

    /**
     * Show edit form.
     */
    public function edit(Bus $bus)
    {
        $this->authorizeBus($bus);

        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        $drivers = $this->availableDrivers($school, $bus);
        $routes = $this->availableRoutes($school, $bus);

        $bus->load(['routes', 'drivers']);

        return view('buses.edit', compact('bus', 'school', 'schools', 'drivers', 'routes'));
    }

    /**
     * Update bus.
     */
    public function update(Request $request, Bus $bus)
    {
        $this->authorizeBus($bus);

        $user = Auth::user();

        $rules = $this->validationRules($bus);

        if (! $this->isSchoolLevelAdmin($user)) {
            $rules['school_id'] = [
                'required',
                'exists:schools,id',
            ];
        }

        $validated = $request->validate($rules);

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $validated['school_id'] = $schoolId;
            } else {
                $validated['school_id'] = $bus->school_id;
            }
        }

        $routeIds = $validated['route_ids'] ?? [];
        unset($validated['route_ids']);
        $driverIds = $validated['driver_ids'] ?? [];
        unset($validated['driver_ids']);

        try {
            DB::transaction(function () use ($bus, $validated, $routeIds, $driverIds) {
                $bus->update($validated);
                $bus->routes()->sync($routeIds);
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

            'route_ids' => 'nullable|array',
            'route_ids.*' => 'exists:routes,id',

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
     * Routes the current user may assign to a bus.
     */
    private function availableRoutes(?School $school, ?Bus $bus = null): Collection
    {
        $user = Auth::user();

        $query = Route::query()->with('school');

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

        return $query->orderBy('name')->get();
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
