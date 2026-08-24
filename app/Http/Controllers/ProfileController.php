<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Fill everything except the uploaded file (validated() would carry
        // the raw UploadedFile instance, not the stored path).
        $user->fill($request->safe()->except(['profile_photo']));

        // A newly uploaded photo replaces the old one on disk.
        if ($request->hasFile('profile_photo')) {
            if (
                $user->profile_photo &&
                Storage::disk('public')->exists($user->getOriginal('profile_photo'))
            ) {
                Storage::disk('public')
                    ->delete($user->getOriginal('profile_photo'));
            }

            $user->profile_photo =
                $request->file('profile_photo')
                    ->store('users', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('name')) {
            $this->syncNameToProfiles($user);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function syncNameToProfiles(User $user): void
    {
        if ($user->driver) {
            [$firstName, $lastName] = array_pad(
                preg_split('/\s+/', trim($user->name), 2),
                2,
                ''
            );

            $user->driver()->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }

        if ($user->parent) {
            $user->parent()->update([
                'name' => $user->name,
            ]);
        }
    }
}
