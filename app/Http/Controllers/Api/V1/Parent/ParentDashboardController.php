<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        $children = $parent->children()
            ->with(['route'])
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        $todayRecords = Attendance::query()
            ->whereIn('student_id', $children->pluck('id'))
            ->whereDate('date', now())
            ->get()
            ->keyBy(fn ($record) => $record->student_id.'-'.$record->trip);

        return response()->json([
            'message' => 'Parent dashboard data.',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'name' => $parent->user->name,
                    'email' => $parent->user->email,
                    'phone' => $parent->phone,
                    'photo_url' => $parent->user->profile_photo
                        ? asset('storage/'.$parent->user->profile_photo)
                        : null,
                    'school' => $parent->school ? [
                        'id' => $parent->school->id,
                        'name' => $parent->school->name,
                        'address' => $parent->school->address,
                    ] : null,
                ],
                'children_count' => $children->count(),
                'children' => $children->map(fn ($student) => [
                    'id' => $student->id,
                    'admission_no' => $student->admission_no,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'roll_no' => $student->roll_no,
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                    'pickup_location' => $student->pickup_location,
                    'drop_location' => $student->drop_location,
                    'route' => $student->route ? [
                        'id' => $student->route->id,
                        'name' => $student->route->name,
                        'route_code' => $student->route->route_code,
                        'is_active' => $student->route->is_active,
                    ] : null,
                    'today_attendance' => $this->todayAttendanceFor(
                        $todayRecords->get($student->id.'-'.Attendance::TRIP_HOME_TO_SCHOOL),
                        $todayRecords->get($student->id.'-'.Attendance::TRIP_SCHOOL_TO_HOME),
                    ),
                ]),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Parent profile data.',
            'data' => $this->profileData($parent),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($parent->user_id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $parent) {
            $user = $parent->user;

            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $parent->update(collect($validated)->except('email')->all());
        });

        return response()->json([
            'message' => 'Parent profile updated.',
            'data' => $this->profileData($parent),
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $parent->user;

        if (
            $user->profile_photo &&
            Storage::disk('public')->exists($user->profile_photo)
        ) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->profile_photo = $request
            ->file('profile_photo')
            ->store('users', 'public');
        $user->save();

        return response()->json([
            'message' => 'Profile photo updated.',
            'data' => [
                'photo_url' => asset('storage/'.$user->profile_photo),
            ],
        ]);
    }

    private function profileData(ParentProfile $parent): array
    {
        return [
            'id' => $parent->id,
            'name' => $parent->user->name,
            'email' => $parent->user->email,
            'phone' => $parent->phone,
            'alternate_phone' => $parent->alternate_phone,
            'address' => $parent->address,
            'occupation' => $parent->occupation,
            'photo_url' => $parent->user->profile_photo
                ? asset('storage/'.$parent->user->profile_photo)
                : null,
            'role' => $parent->user->getRoleNames()->first(),
            'status' => $parent->user->status,
            'school' => $parent->school ? [
                'id' => $parent->school->id,
                'name' => $parent->school->name,
                'address' => $parent->school->address,
            ] : null,
            'children_count' => $parent->children()->count(),
        ];
    }

    private function todayAttendanceFor(?Attendance $home, ?Attendance $school): array
    {
        $tripStatus = fn (?Attendance $record) => $record === null
            ? 'not_checked_in'
            : ($record->isCheckedOut() ? 'completed' : 'checked_in');

        $nextAction = null;

        if (! $home || ! $home->isCheckedIn()) {
            $nextAction = ['key' => 'picked_up_home', 'label' => 'Pick Up'];
        } elseif (! $home->isCheckedOut()) {
            $nextAction = ['key' => 'dropped_at_school', 'label' => 'Drop at School'];
        } elseif (! $school || ! $school->isCheckedIn()) {
            $nextAction = ['key' => 'picked_up_school', 'label' => 'Pick Up from School'];
        } elseif (! $school->isCheckedOut()) {
            $nextAction = ['key' => 'dropped_at_home', 'label' => 'Drop at Home'];
        }

        return [
            'home_to_school' => [
                'check_in_at' => $home?->check_in_at?->toIso8601String(),
                'check_out_at' => $home?->check_out_at?->toIso8601String(),
                'status' => $tripStatus($home),
            ],
            'school_to_home' => [
                'check_in_at' => $school?->check_in_at?->toIso8601String(),
                'check_out_at' => $school?->check_out_at?->toIso8601String(),
                'status' => $tripStatus($school),
            ],
            'completed' => $nextAction === null,
            'next_action' => $nextAction,
        ];
    }
}
