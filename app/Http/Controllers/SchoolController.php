<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $schools = School::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('schools.index', compact('schools', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('schools.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request
                ->file('logo')
                ->store('schools', 'public');
        }

        School::create($validated);

        return redirect()
            ->route('schools.index')
            ->with('success', 'School created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        $totalStudents = $school->students()->count();
        $activeStudents = $school->students()->where('is_active', true)->count();

        $totalDrivers = Driver::where('school_id', $school->id)->count();
        $activeDrivers = Driver::where('school_id', $school->id)->where('status', 'Active')->count();

        $totalBuses = Bus::where('school_id', $school->id)->count();
        $activeBuses = Bus::where('school_id', $school->id)->where('status', 'Active')->count();
        $maintenanceBuses = Bus::where('school_id', $school->id)->where('status', 'Maintenance')->count();
        $inactiveBuses = Bus::where('school_id', $school->id)->where('status', 'Inactive')->count();

        $totalRoutes = Route::where('school_id', $school->id)->count();
        $activeRoutes = Route::where('school_id', $school->id)->where('is_active', true)->count();

        $totalStops = Route::where('school_id', $school->id)
            ->withCount('stops')
            ->get()
            ->sum('stops_count');

        $totalParents = ParentProfile::where('school_id', $school->id)->count();
        $totalSchoolAdmins = SchoolAdmin::where('school_id', $school->id)->count();

        return view('schools.show', compact(
            'school',
            'totalStudents',
            'activeStudents',
            'totalDrivers',
            'activeDrivers',
            'totalBuses',
            'activeBuses',
            'maintenanceBuses',
            'inactiveBuses',
            'totalRoutes',
            'activeRoutes',
            'totalStops',
            'totalParents',
            'totalSchoolAdmins',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        return view('schools.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request
                ->file('logo')
                ->store('schools', 'public');
        }

        $school->update($validated);

        return redirect()
            ->route('schools.index')
            ->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $school->delete();

        return redirect()
            ->route('schools.index')
            ->with('success', 'School deleted successfully.');
    }
}
