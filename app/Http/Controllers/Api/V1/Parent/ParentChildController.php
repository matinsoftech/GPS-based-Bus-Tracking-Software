<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ParentChildController extends Controller
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
            ->with(['bus.routes', 'bus.driver'])
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        $todayRecords = Attendance::query()
            ->whereIn('student_id', $children->pluck('id'))
            ->whereDate('date', now())
            ->get()
            ->keyBy(fn ($record) => $record->student_id.'-'.$record->trip);

        return response()->json([
            'message' => 'Parent children data.',
            'data' => [
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
                    'is_active' => $student->is_active,
                    'bus' => $student->bus ? [
                        'id' => $student->bus->id,
                        'bus_number' => $student->bus->bus_number,
                        'registration_number' => $student->bus->registration_number,
                        'status' => $student->bus->status,
                        'routes' => $student->bus->routes->map(fn ($route) => [
                            'id' => $route->id,
                            'name' => $route->name,
                        ]),
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

    public function show(Request $request, Student $student)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        if ($parent->children()->whereKey($student->id)->doesntExist()) {
            return response()->json([
                'message' => 'You are not authorized to view this student.',
            ], 403);
        }

        $student->load(['school', 'bus.routes', 'bus.driver']);

        $todayRecords = Attendance::query()
            ->where('student_id', $student->id)
            ->whereDate('date', now())
            ->get()
            ->keyBy('trip');

        return response()->json([
            'message' => 'Parent child data.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'admission_no' => $student->admission_no,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'roll_no' => $student->roll_no,
                    'date_of_birth' => $student->date_of_birth?->toDateString(),
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                    'pickup_location' => $student->pickup_location,
                    'drop_location' => $student->drop_location,
                    'pickup_latitude' => $student->pickup_latitude,
                    'pickup_longitude' => $student->pickup_longitude,
                    'drop_latitude' => $student->drop_latitude,
                    'drop_longitude' => $student->drop_longitude,
                    'is_active' => $student->is_active,
                    'school' => $student->school ? [
                        'id' => $student->school->id,
                        'name' => $student->school->name,
                        'address' => $student->school->address,
                    ] : null,
                ],
                'bus' => $student->bus ? [
                    'id' => $student->bus->id,
                    'bus_number' => $student->bus->bus_number,
                    'registration_number' => $student->bus->registration_number,
                    'make' => $student->bus->make,
                    'model' => $student->bus->model,
                    'year' => $student->bus->year,
                    'capacity' => $student->bus->capacity,
                    'fuel_type' => $student->bus->fuel_type,
                    'status' => $student->bus->status,
                    'routes' => $student->bus->routes->map(fn ($route) => [
                        'id' => $route->id,
                        'name' => $route->name,
                        'route_code' => $route->route_code,
                        'start_location' => $route->start_location,
                        'end_location' => $route->end_location,
                    ]),
                    'driver' => $student->bus->driver ? [
                        'id' => $student->bus->driver->id,
                        'name' => $student->bus->driver->full_name,
                        'phone' => $student->bus->driver->phone,
                    ] : null,
                ] : null,
                'today_attendance' => $this->todayAttendanceFor(
                    $todayRecords->get(Attendance::TRIP_HOME_TO_SCHOOL),
                    $todayRecords->get(Attendance::TRIP_SCHOOL_TO_HOME),
                ),
            ],
        ]);
    }

    public function history(Request $request, Student $student)
    {
        $parent = $request->user()->parent;

        if (! $parent) {
            return response()->json([
                'message' => 'Parent profile not found.',
            ], 404);
        }

        if ($parent->children()->whereKey($student->id)->doesntExist()) {
            return response()->json([
                'message' => 'You are not authorized to view this student.',
            ], 403);
        }

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = ! empty($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = ! empty($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : now()->endOfDay();

        $records = Attendance::query()
            ->with(['bus', 'markedBy'])
            ->where('student_id', $student->id)
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50);

        return response()->json([
            'message' => 'Parent child attendance history.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'grade' => $student->grade,
                    'section' => $student->section,
                    'photo' => $student->photo ? asset('storage/'.$student->photo) : null,
                ],
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'total_records' => $records->total(),
                'records' => $records->map(fn (Attendance $record) => [
                    'id' => $record->id,
                    'date' => $record->date?->toDateString(),
                    'trip' => $record->trip,
                    'trip_label' => $record->tripLabel(),
                    'check_in_at' => $record->check_in_at?->toIso8601String(),
                    'check_out_at' => $record->check_out_at?->toIso8601String(),
                    'status' => $record->isCheckedOut()
                        ? 'completed'
                        : ($record->isCheckedIn() ? 'checked_in' : 'not_checked_in'),
                    'marked_by' => $record->markedBy ? [
                        'id' => $record->markedBy->id,
                        'name' => $record->markedBy->name,
                    ] : null,
                ]),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'per_page' => $records->perPage(),
                    'last_page' => $records->lastPage(),
                    'total' => $records->total(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                ],
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
