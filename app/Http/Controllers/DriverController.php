<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class DriverController extends Controller
{
    /**
     * Display drivers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Driver::with('school');

        /*
        |--------------------------------------------------------------------------
        | School-level admins can only see drivers from their school.
        |--------------------------------------------------------------------------
        */

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('drivers.index', compact('drivers'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | School-level admins automatically get their school.
        |--------------------------------------------------------------------------
        */

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        $schoolIdForAssets = $school?->id ?? $user->school_id ?? null;

        $buses = $schoolIdForAssets
            ? Bus::where('school_id', $schoolIdForAssets)->orderBy('bus_number')->get()
            : Bus::orderBy('bus_number')->get();

        $routes = $schoolIdForAssets
            ? Route::where('school_id', $schoolIdForAssets)->orderBy('name')->get()
            : Route::orderBy('name')->get();

        return view('drivers.create', compact('school', 'schools', 'buses', 'routes'));
    }

    /**
     * Store driver.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [

            'first_name' => 'required|string|max:100',

            'last_name' => 'required|string|max:100',

            'gender' => [
                'required',
                Rule::in(['Male', 'Female', 'Other']),
            ],

            'date_of_birth' => 'required|date|before:today',

            'phone' => ['required', 'regex:/^[+]?[\d\s\-\(\)]{10,20}$/'],

            'email' => 'required|email|max:255|unique:users,email',

            'password' => 'required|min:8',

            'address' => 'required|string',

            'city' => 'nullable|string|max:100',

            'state' => 'nullable|string|max:100',

            'country' => 'nullable|string|max:100',

            'postal_code' => 'nullable|string|max:20',

            'license_number' => [
                'required',
                'string',
                'max:100',
                'unique:drivers,license_number',
            ],

            'license_type' => 'required|string|max:100',

            'license_issue_date' => 'required|date',

            'license_expiry_date' => [
                'required',
                'date',
                'after:today',
            ],

            'experience_years' => 'nullable|integer|min:0|max:80',

            'joining_date' => 'required|date',

            'status' => [
                'required',
                Rule::in(['Active', 'Inactive', 'Suspended']),
            ],

            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'emergency_contact_name' => 'nullable|string|max:100',

            'emergency_contact_phone' => ['nullable', 'regex:/^[+]?[\d\s\-\(\)]{10,20}$/'],

            'remarks' => 'nullable|string',

            'bus_ids' => 'nullable|array',
            'bus_ids.*' => 'exists:buses,id',

            'route_ids' => 'nullable|array',
            'route_ids.*' => 'exists:routes,id',
        ];

        /*
        |--------------------------------------------------------------------------
        | School validation
        |--------------------------------------------------------------------------
        */

        if (! $this->isSchoolLevelAdmin($user)) {

            $rules['school_id'] = [
                'nullable',
                'exists:schools,id',
            ];
        }

        $validated = $request->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | Assign school automatically
        |--------------------------------------------------------------------------
        */

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
                    ->withErrors(['school_id' => 'Please create a school before adding a driver.'])
                    ->withInput();
            }
        }

        $validated['employee_id'] = $request->input('employee_id') ?: 'DRV-'.now()->format('YmdHis').'-'.random_int(1000, 9999);

        /*
        |--------------------------------------------------------------------------
        | Profile photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('profile_photo')) {

            $validated['profile_photo'] =
                $request->file('profile_photo')
                    ->store('drivers', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Created by
        |--------------------------------------------------------------------------
        */

        $validated['created_by'] = $user->id;

        /*
        |--------------------------------------------------------------------------
        | Create login account and link it to the driver
        |--------------------------------------------------------------------------
        */

        try {
            DB::transaction(function () use ($validated, $request) {
                $user = User::create([
                    'name' => trim($validated['first_name'].' '.$validated['last_name']),
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'school_id' => $validated['school_id'],
                ]);

                $user->assignRole('Driver');

                $driver = Driver::create([
                    ...$validated,
                    'user_id' => $user->id,
                ]);

                if ($request->filled('bus_ids')) {
                    $driver->buses()->sync($request->input('bus_ids'));
                }

                if ($request->filled('route_ids')) {
                    $driver->routes()->sync($request->input('route_ids'));
                }
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create driver.']);
        }

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver added successfully.');
    }

    /**
     * Display driver.
     */
    public function show(Driver $driver)
    {
        $this->authorizeDriver($driver);

        $driver->load([
            'school',
            'creator',
        ]);

        return view('drivers.show', compact('driver'));
    }

    /**
     * Show edit form.
     */
    public function edit(Driver $driver)
    {
        $this->authorizeDriver($driver);

        $driver->load(['buses', 'routes']);

        $user = Auth::user();

        $schoolIdForAssets = $driver->school_id;

        $buses = Bus::where('school_id', $schoolIdForAssets)->orderBy('bus_number')->get();
        $routes = Route::where('school_id', $schoolIdForAssets)->orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::findOrFail($schoolId);

                return view('drivers.edit', compact(
                    'driver',
                    'school',
                    'buses',
                    'routes',
                ));
            }
        }

        $schools = School::orderBy('name')->get();

        return view('drivers.edit', compact(
            'driver',
            'schools',
            'buses',
            'routes',
        ));
    }

    /**
     * Update driver.
     */
    public function update(Request $request, Driver $driver)
    {
        $this->authorizeDriver($driver);

        $user = Auth::user();

        $rules = [

            'employee_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('drivers', 'employee_id')
                    ->ignore($driver->id),
            ],

            'first_name' => 'required|string|max:100',

            'last_name' => 'required|string|max:100',

            'gender' => [
                'required',
                Rule::in(['Male', 'Female', 'Other']),
            ],

            'date_of_birth' => 'required|date|before:today',

            'phone' => ['required', 'regex:/^[+]?[\d\s\-\(\)]{10,20}$/'],

            'email' => 'required|email|max:255|unique:users,email,'.$driver->user_id,

            'password' => [
                'nullable',
                Rule::requiredIf(! $driver->user),
                'min:8',
            ],

            'address' => 'required|string',

            'city' => 'nullable|string|max:100',

            'state' => 'nullable|string|max:100',

            'country' => 'nullable|string|max:100',

            'postal_code' => 'nullable|string|max:20',

            'license_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('drivers', 'license_number')
                    ->ignore($driver->id),
            ],

            'license_type' => 'required|string|max:100',

            'license_issue_date' => 'required|date',

            'license_expiry_date' => [
                'required',
                'date',
            ],

            'experience_years' => 'nullable|integer|min:0|max:80',

            'joining_date' => 'required|date',

            'status' => [
                'required',
                Rule::in(['Active', 'Inactive', 'Suspended']),
            ],

            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'emergency_contact_name' => 'nullable|string|max:100',

            'emergency_contact_phone' => ['nullable', 'regex:/^[+]?[\d\s\-\(\)]{10,20}$/'],

            'remarks' => 'nullable|string',

            'bus_ids' => 'nullable|array',
            'bus_ids.*' => 'exists:buses,id',

            'route_ids' => 'nullable|array',
            'route_ids.*' => 'exists:routes,id',
        ];

        if (! $this->isSchoolLevelAdmin($user)) {

            $rules['school_id'] = [
                'required',
                'exists:schools,id',
            ];
        }

        $validated = $request->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | Principal cannot move driver to another school
        |--------------------------------------------------------------------------
        */

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $validated['school_id'] = $schoolId;
            } elseif (! empty($request->input('school_id'))) {
                $validated['school_id'] = $request->input('school_id');
            } elseif ($driver->school_id) {
                $validated['school_id'] = $driver->school_id;
            } else {
                $validated['school_id'] = School::query()->value('id') ?? 1;
            }
        } elseif (! empty($request->input('school_id'))) {
            $validated['school_id'] = $request->input('school_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Replace profile photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('profile_photo')) {

            if (
                $driver->profile_photo &&
                Storage::disk('public')->exists($driver->profile_photo)
            ) {
                Storage::disk('public')
                    ->delete($driver->profile_photo);
            }

            $validated['profile_photo'] =
                $request->file('profile_photo')
                    ->store('drivers', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Create or sync the login account linked to the driver
        |--------------------------------------------------------------------------
        */

        try {
            DB::transaction(function () use ($driver, $validated, $request) {
                $name = trim($validated['first_name'].' '.$validated['last_name']);

                if (! $driver->user) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $validated['email'],
                        'password' => $validated['password'],
                        'school_id' => $validated['school_id'],
                    ]);

                    $user->assignRole('Driver');

                    $validated['user_id'] = $user->id;
                } else {
                    $driver->user->update([
                        'name' => $name,
                        'email' => $validated['email'],
                        'password' => $validated['password'] ?? $driver->user->password,
                        'school_id' => $validated['school_id'],
                    ]);
                }

                $driver->update($validated);

                $driver->buses()->sync($request->input('bus_ids', []));
                $driver->routes()->sync($request->input('route_ids', []));
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update driver.']);
        }

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver updated successfully.');
    }

    /**
     * Delete driver.
     */
    public function destroy(Driver $driver)
    {
        $this->authorizeDriver($driver);

        /*
        |--------------------------------------------------------------------------
        | Delete profile photo
        |--------------------------------------------------------------------------
        */

        if (
            $driver->profile_photo &&
            Storage::disk('public')->exists($driver->profile_photo)
        ) {
            Storage::disk('public')
                ->delete($driver->profile_photo);
        }

        try {
            DB::transaction(function () use ($driver) {
                $driver->user?->delete();
                $driver->delete();
            });
        } catch (Throwable $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete driver.']);
        }

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }

    /**
     * Make sure Principal can only access their own school drivers.
     */
    private function authorizeDriver(Driver $driver)
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $driver->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this driver.');
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
