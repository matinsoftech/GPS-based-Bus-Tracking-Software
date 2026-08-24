<?php

namespace App\Http\Controllers;

use App\Models\ParentProfile;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ParentProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ParentProfile::query()->with(['user', 'school', 'children']);

        /*
        |--------------------------------------------------------------------------
        | School-level admins can only see parents from their school.
        |--------------------------------------------------------------------------
        */

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        $search = $request->search;

        $parents = $query
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('parents.index', compact('parents', 'search'));
    }

    /**
     * Show the children belonging to the logged-in parent.
     */
    public function myChildren()
    {
        $user = Auth::user();

        $parentProfile = ParentProfile::query()
            ->where('user_id', $user->id)
            ->with('children.school')
            ->first();

        $children = $parentProfile?->children ?? collect();

        return view('parents.my-children', compact('children'));
    }

    /**
     * Show the form for creating a new resource.
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

        return view('parents.create', compact('school', 'schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|max:20',
            'alternate_phone' => 'nullable|max:20',
            'address' => 'required',
            'occupation' => 'nullable|max:255',
        ];

        if ($this->isSchoolLevelAdmin($user)) {
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

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'school_id' => $validated['school_id'],
                ]);

                $user->assignRole('Parent');

                ParentProfile::create([
                    'user_id' => $user->id,
                    'school_id' => $validated['school_id'],
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'alternate_phone' => $validated['alternate_phone'] ?? null,
                    'address' => $validated['address'],
                    'occupation' => $validated['occupation'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create parent profile.']);
        }

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent profile created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentProfile $parentProfile)
    {
        $this->authorizeParent($parentProfile);

        $parentProfile->load(['user', 'school']);

        return view('parents.show', compact('parentProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParentProfile $parentProfile)
    {
        $this->authorizeParent($parentProfile);

        $user = Auth::user();
        $school = null;
        $schools = School::orderBy('name')->get();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $school = School::find($schoolId);
            }
        }

        $parentProfile->load(['user', 'school']);

        return view('parents.edit', compact('parentProfile', 'school', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentProfile $parentProfile)
    {
        $this->authorizeParent($parentProfile);

        $user = Auth::user();

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$parentProfile->user_id,
            'password' => 'nullable|min:8',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|max:20',
            'alternate_phone' => 'nullable|max:20',
            'address' => 'required',
            'occupation' => 'nullable|max:255',
        ];

        if ($this->isSchoolLevelAdmin($user)) {
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
            } else {
                $validated['school_id'] = $parentProfile->school_id;
            }
        }

        try {
            DB::transaction(function () use ($validated, $parentProfile) {
                $parentProfile->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'] ?? $parentProfile->user->password,
                    'school_id' => $validated['school_id'],
                ]);

                $parentProfile->update([
                    'school_id' => $validated['school_id'],
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'alternate_phone' => $validated['alternate_phone'] ?? null,
                    'address' => $validated['address'],
                    'occupation' => $validated['occupation'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update parent profile.']);
        }

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentProfile $parentProfile)
    {
        $this->authorizeParent($parentProfile);

        try {
            DB::transaction(function () use ($parentProfile) {
                $parentProfile->user->delete();
                $parentProfile->delete();
            });
        } catch (Throwable $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete parent profile.']);
        }

        return redirect()
            ->route('parents.index')
            ->with('success', 'Parent profile deleted successfully.');
    }

    /**
     * Make sure school-level admins can only access their own school's parents.
     */
    private function authorizeParent(ParentProfile $parentProfile): void
    {
        $user = Auth::user();

        if ($this->isSchoolLevelAdmin($user)) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && $parentProfile->school_id != $schoolId) {
                abort(403, 'You are not authorized to access this parent.');
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
