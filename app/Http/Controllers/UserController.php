<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * Supports searching by name/email and filtering by role.
     * Uses the Eloquent `roles` relationship (no raw SQL).
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $selectedRole = $request->role;
        $selectedSchool = $request->school_id;

        $users = User::query()
            ->with('roles', 'school')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($selectedRole, function ($query) use ($selectedRole) {
                $query->whereHas('roles', function ($query) use ($selectedRole) {
                    $query->where('name', $selectedRole);
                });
            })
            ->when($selectedSchool, function ($query) use ($selectedSchool) {
                $query->where('school_id', $selectedSchool);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();
        $schools = School::orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'search', 'selectedRole', 'schools', 'selectedSchool'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('profile_photo')) {

            $validated['profile_photo'] =
                $request->file('profile_photo')
                    ->store('users', 'public');
        }

        try {
            // Create the user and assign the chosen role atomically.
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'status' => $validated['status'],
                    'profile_photo' => $validated['profile_photo'] ?? null,
                ]);

                $user->assignRole($validated['role']);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user.']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // A blank password field means "keep the current password".
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // A newly uploaded photo replaces the old one on disk.
        if ($request->hasFile('profile_photo')) {
            if (
                $user->profile_photo &&
                Storage::disk('public')->exists($user->profile_photo)
            ) {
                Storage::disk('public')
                    ->delete($user->profile_photo);
            }

            $validated['profile_photo'] =
                $request->file('profile_photo')
                    ->store('users', 'public');
        }

        try {
            // Update the user and synchronise the role atomically.
            DB::transaction(function () use ($validated, $user) {
                $user->update($validated);
                $user->syncRoles([$validated['role']]);
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user.']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove (soft delete) the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent a Super Admin from locking themselves out of the app.
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if (
            $user->profile_photo &&
            Storage::disk('public')->exists($user->profile_photo)
        ) {
            Storage::disk('public')
                ->delete($user->profile_photo);
        }

        try {
            // Soft delete keeps the row (and linked profiles) intact.
            $user->delete();
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to delete user.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
