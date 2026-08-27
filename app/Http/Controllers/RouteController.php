<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    /**
     * Display a listing of routes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Route::with('school', 'activeTrip.bus');

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('route_code', 'like', "%{$search}%")
                    ->orWhere('start_location', 'like', "%{$search}%")
                    ->orWhere('end_location', 'like', "%{$search}%");
            });
        }

        $routes = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new route.
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

        return view('routes.create', compact('school', 'schools'));
    }

    /**
     * Store a newly created route.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'estimated_distance' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        if (! $this->isSchoolLevelAdmin($user)) {
            $rules['school_id'] = 'required|exists:schools,id';
        }

        $validated = $request->validate($rules);

        if ($this->isSchoolLevelAdmin($user)) {
            $validated['school_id'] = $this->getUserSchoolId($user);
        }

        $validated['is_active'] = $request->boolean('is_active');

        Route::create($validated);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route created successfully.');
    }

    /**
     * Display the specified route.
     */
    public function show(Route $route)
    {
        $this->authorizeRoute($route);

        $route->load(['school', 'activeTrip.bus.drivers']);

        return view('routes.show', compact('route'));
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit(Route $route)
    {
        $this->authorizeRoute($route);

        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        return view('routes.edit', compact('route', 'school', 'schools'));
    }

    /**
     * Update the specified route.
     */
    public function update(Request $request, Route $route)
    {
        $this->authorizeRoute($route);

        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code,'.$route->id,
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'estimated_distance' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        if (! $this->isSchoolLevelAdmin($user)) {
            $rules['school_id'] = 'required|exists:schools,id';
        }

        $validated = $request->validate($rules);

        if ($this->isSchoolLevelAdmin($user)) {
            $validated['school_id'] = $this->getUserSchoolId($user);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $route->update($validated);

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified route.
     */
    public function destroy(Route $route)
    {
        $this->authorizeRoute($route);

        $route->delete();

        return redirect()
            ->route('routes.index')
            ->with('success', 'Route deleted successfully.');
    }

    /**
     * Make sure school-level admins can only access their own school's routes.
     */
    private function authorizeRoute(Route $route): void
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $route->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this route.');
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
