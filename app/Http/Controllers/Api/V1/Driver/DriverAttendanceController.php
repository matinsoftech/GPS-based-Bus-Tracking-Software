<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DriverAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['required', 'integer'],
        ]);

        $bus = $driver->buses()
            ->with(['routes', 'students.parent.user'])
            ->find($validated['bus_id']);

        if (! $bus) {
            return response()->json([
                'message' => 'Bus not found for this driver.',
            ], 404);
        }

        $students = $bus->students()
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        $todayRecords = Attendance::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereDate('date', now())
            ->get()
            ->keyBy(fn ($record) => $record->student_id.'-'.$record->trip);

        return response()->json([
            'message' => 'Students for driver bus.',
            'data' => [
                'bus' => [
                    'id' => $bus->id,
                    'bus_number' => $bus->bus_number,
                    'registration_number' => $bus->registration_number,
                    'status' => $bus->status,
                    'routes' => $bus->routes->map(fn ($route) => [
                        'id' => $route->id,
                        'name' => $route->name,
                    ]),
                ],
                'total_students' => $students->count(),
                'students' => $students->map(fn ($student) => [
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
                    'parent' => $student->parent ? [
                        'id' => $student->parent->id,
                        'name' => $student->parent->user->name ?? $student->parent->name,
                        'phone' => $student->parent->phone,
                    ] : null,
                    'today_attendance' => $this->todayAttendanceFor(
                        $todayRecords->get($student->id.'-'.Attendance::TRIP_HOME_TO_SCHOOL),
                        $todayRecords->get($student->id.'-'.Attendance::TRIP_SCHOOL_TO_HOME),
                    ),
                ]),
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

    public function history(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['required', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $bus = $driver->buses()
            ->with('routes')
            ->find($validated['bus_id']);

        if (! $bus) {
            return response()->json([
                'message' => 'Bus not found for this driver.',
            ], 404);
        }

        $from = ! empty($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = ! empty($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : now()->endOfDay();

        $records = Attendance::query()
            ->with(['student', 'markedBy'])
            ->where('bus_id', $bus->id)
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50);

        return response()->json([
            'message' => 'Driver bus attendance history.',
            'data' => [
                'bus' => [
                    'id' => $bus->id,
                    'bus_number' => $bus->bus_number,
                    'registration_number' => $bus->registration_number,
                    'status' => $bus->status,
                    'routes' => $bus->routes->map(fn ($route) => [
                        'id' => $route->id,
                        'name' => $route->name,
                    ]),
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
                    'student' => $record->student ? [
                        'id' => $record->student->id,
                        'admission_no' => $record->student->admission_no,
                        'full_name' => $record->student->full_name,
                        'grade' => $record->student->grade,
                        'section' => $record->student->section,
                        'photo' => $record->student->photo ? asset('storage/'.$record->student->photo) : null,
                    ] : null,
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

    public function markAttendance(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'bus_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);

        $bus = $driver->buses()->find($validated['bus_id']);

        if (! $bus) {
            return response()->json([
                'message' => 'Bus not found for this driver.',
            ], 404);
        }

        if ($bus->status !== 'Active') {
            return response()->json([
                'message' => 'Attendance can only be marked on active buses.',
            ], 422);
        }

        $student = Student::where('id', $validated['student_id'])
            ->where('bus_id', $bus->id)
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'Student not found on this bus.',
            ], 422);
        }

        $date = now();

        $records = Attendance::query()
            ->where('student_id', $student->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('trip');

        $home = $records->get(Attendance::TRIP_HOME_TO_SCHOOL);
        $school = $records->get(Attendance::TRIP_SCHOOL_TO_HOME);

        $action = null;

        if (! $home || ! $home->isCheckedIn()) {
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $date,
                    'trip' => Attendance::TRIP_HOME_TO_SCHOOL,
                ],
                [
                    'bus_id' => $bus->id,
                    'check_in_at' => $date,
                    'marked_by' => $request->user()->id,
                ]
            );

            $action = ['key' => 'picked_up_home', 'message' => "{$student->full_name} picked up from home."];
        } elseif (! $home->isCheckedOut()) {
            $home->update([
                'check_out_at' => $date,
                'marked_by' => $request->user()->id,
            ]);

            $attendance = $home;
            $action = ['key' => 'dropped_at_school', 'message' => "{$student->full_name} dropped at school."];
        } elseif (! $school || ! $school->isCheckedIn()) {
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $date,
                    'trip' => Attendance::TRIP_SCHOOL_TO_HOME,
                ],
                [
                    'bus_id' => $bus->id,
                    'check_in_at' => $date,
                    'marked_by' => $request->user()->id,
                ]
            );

            $action = ['key' => 'picked_up_school', 'message' => "{$student->full_name} picked up from school."];
        } elseif (! $school->isCheckedOut()) {
            $school->update([
                'check_out_at' => $date,
                'marked_by' => $request->user()->id,
            ]);

            $attendance = $school;
            $action = ['key' => 'dropped_at_home', 'message' => "{$student->full_name} dropped at home."];
        } else {
            return response()->json([
                'message' => "{$student->full_name}'s attendance is already completed for today.",
            ], 422);
        }

        return response()->json([
            'message' => $action['message'],
            'data' => [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'bus_id' => $attendance->bus_id,
                'trip' => $attendance->trip,
                'date' => $attendance->date?->toDateString(),
                'check_in_at' => $attendance->check_in_at?->toIso8601String(),
                'check_out_at' => $attendance->check_out_at?->toIso8601String(),
                'marked_by' => $attendance->marked_by,
            ],
        ]);
    }
}
