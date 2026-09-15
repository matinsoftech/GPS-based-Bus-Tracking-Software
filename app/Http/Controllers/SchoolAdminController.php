<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SchoolAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->guardOwnSchool();

        $search = $request->search;
        $selectedSchool = $this->isSuperAdmin() ? $request->school_id : null;

        $schoolAdmins = SchoolAdmin::query()
            ->with(['user', 'school'])
            ->when(! $this->isSuperAdmin(), function ($query) {
                $query->where('school_id', $this->ownSchoolId());
            })
            ->when($selectedSchool, function ($query) use ($selectedSchool) {
                $query->where('school_id', $selectedSchool);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('school', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = $this->isSuperAdmin()
            ? School::orderBy('name')->get()
            : collect();

        return view('school-admins.index', compact('schoolAdmins', 'search', 'schools', 'selectedSchool'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->guardOwnSchool();

        $superAdmin = $this->isSuperAdmin();

        $schools = $superAdmin
            ? School::orderBy('name')->get()
            : School::where('id', $this->ownSchoolId())->orderBy('name')->get();

        return view('school-admins.create', compact('schools', 'superAdmin'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->guardOwnSchool();

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|max:20',
            'designation' => 'nullable|max:255',
            'address' => 'nullable',
        ]);

        $schoolId = $this->isSuperAdmin() ? $validated['school_id'] : $this->ownSchoolId();

        try {
            DB::transaction(function () use ($validated, $schoolId) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'school_id' => $schoolId,
                ]);

                $user->assignRole('School Admin');

                SchoolAdmin::create([
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'designation' => $validated['designation'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create school admin.']);
        }

        return redirect()
            ->route('school-admins.index')
            ->with('success', 'School admin created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolAdmin $schoolAdmin)
    {
        $this->authorizeSchool($schoolAdmin);

        $schoolAdmin->load(['user', 'school']);

        return view('school-admins.show', compact('schoolAdmin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolAdmin $schoolAdmin)
    {
        $this->guardOwnSchool();
        $this->authorizeSchool($schoolAdmin);

        $superAdmin = $this->isSuperAdmin();

        $schoolAdmin->load(['user', 'school']);

        $schools = $superAdmin
            ? School::orderBy('name')->get()
            : School::where('id', $this->ownSchoolId())->orderBy('name')->get();

        return view('school-admins.edit', compact('schoolAdmin', 'schools', 'superAdmin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolAdmin $schoolAdmin)
    {
        $this->guardOwnSchool();
        $this->authorizeSchool($schoolAdmin);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$schoolAdmin->user_id,
            'password' => 'nullable|min:8',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|max:20',
            'designation' => 'nullable|max:255',
            'address' => 'nullable',
        ]);

        $schoolId = $this->isSuperAdmin() ? $validated['school_id'] : $this->ownSchoolId();

        try {
            DB::transaction(function () use ($validated, $schoolId, $schoolAdmin) {
                $schoolAdmin->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'] ?? $schoolAdmin->user->password,
                    'school_id' => $schoolId,
                ]);

                $schoolAdmin->update([
                    'school_id' => $schoolId,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'designation' => $validated['designation'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update school admin.']);
        }

        return redirect()
            ->route('school-admins.index')
            ->with('success', 'School admin updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolAdmin $schoolAdmin)
    {
        $this->guardOwnSchool();
        $this->authorizeSchool($schoolAdmin);

        try {
            DB::transaction(function () use ($schoolAdmin) {
                $schoolAdmin->user->delete();
                $schoolAdmin->delete();
            });
        } catch (Throwable $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete school admin.']);
        }

        return redirect()
            ->route('school-admins.index')
            ->with('success', 'School admin deleted successfully.');
    }

    /**
     * Whether the current user is a Super Admin.
     */
    private function isSuperAdmin(): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    /**
     * The school_id of the current user's school admin profile, if any.
     */
    private function ownSchoolId(): ?int
    {
        return SchoolAdmin::where('user_id', auth()->id())->first()?->school_id;
    }

    /**
     * Abort when a non-Super Admin has no assigned school.
     */
    private function guardOwnSchool(): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        abort_if(is_null($this->ownSchoolId()), 403);
    }

    /**
     * Abort when a non-Super Admin tries to access another school's admin.
     */
    private function authorizeSchool(SchoolAdmin $schoolAdmin): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        abort_if($schoolAdmin->school_id !== $this->ownSchoolId(), 403);
    }
}
