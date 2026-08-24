<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

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
            ->with(['bus.route', 'bus.driver'])
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
                    'bus' => $student->bus ? [
                        'id' => $student->bus->id,
                        'bus_number' => $student->bus->bus_number,
                        'registration_number' => $student->bus->registration_number,
                        'status' => $student->bus->status,
                        'route' => $student->bus->route ? [
                            'id' => $student->bus->route->id,
                            'name' => $student->bus->route->name,
                        ] : null,
                        'driver' => $student->bus->driver ? [
                            'id' => $student->bus->driver->id,
                            'name' => $student->bus->driver->full_name,
                            'phone' => $student->bus->driver->phone,
                        ] : null,
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
            'data' => [
                'id' => $parent->id,
                'name' => $parent->user->name,
                'email' => $parent->user->email,
                'phone' => $parent->phone,
                'alternate_phone' => $parent->alternate_phone,
                'address' => $parent->address,
                'occupation' => $parent->occupation,
                'role' => $parent->user->getRoleNames()->first(),
                'status' => $parent->user->status,
                'school' => $parent->school ? [
                    'id' => $parent->school->id,
                    'name' => $parent->school->name,
                    'address' => $parent->school->address,
                ] : null,
                'children_count' => $parent->children()->count(),
            ],
        ]);
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
