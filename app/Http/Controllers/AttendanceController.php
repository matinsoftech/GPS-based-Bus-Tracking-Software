<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display buses available for attendance.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Bus::query()
            ->with(['school', 'drivers', 'routes'])
            ->withCount('students');

        if ($user->hasRole('Super Admin')) {
            // All buses.
        } elseif ($user->hasAnyRole(['School Admin', 'Principal'])) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
        } elseif ($user->hasRole('Driver')) {
            $driverId = Driver::where('user_id', $user->id)->value('id');

            if (! $driverId) {
                $today = $request->query('date') ?: now()->toDateString();

                return view('attendance.index', [
                    'buses' => collect(),
                    'checkedIn' => collect(),
                    'today' => $today,
                    'groupedBySchool' => false,
                ]);
            }

            $query->whereHas('drivers', fn($q) => $q->where('drivers.id', $driverId));
        }

        $buses = $query->orderBy('bus_number')->get();

        $today = $request->query('date') ?: now()->toDateString();

        $checkedIn = Attendance::query()
            ->whereDate('date', $today)
            ->whereIn('bus_id', $buses->pluck('id'))
            ->get()
            ->groupBy('bus_id')
            ->map(fn ($group) => [
                'picked_up_home' => $group->where('trip', Attendance::TRIP_HOME_TO_SCHOOL)->whereNotNull('check_in_at')->pluck('student_id')->unique()->count(),
                'dropped_school' => $group->where('trip', Attendance::TRIP_HOME_TO_SCHOOL)->whereNotNull('check_out_at')->pluck('student_id')->unique()->count(),
                'picked_up_school' => $group->where('trip', Attendance::TRIP_SCHOOL_TO_HOME)->whereNotNull('check_in_at')->pluck('student_id')->unique()->count(),
                'dropped_home' => $group->where('trip', Attendance::TRIP_SCHOOL_TO_HOME)->whereNotNull('check_out_at')->pluck('student_id')->unique()->count(),
            ]);

        $groupedBySchool = $user->hasRole('Super Admin');

        return view('attendance.index', compact('buses', 'checkedIn', 'today', 'groupedBySchool'));
    }

    /**
     * Display a bus and its assigned students with the next valid attendance action.
     */
    public function show(Request $request, Bus $bus)
    {
        $this->authorizeBus($bus);
        $this->ensureBusActive($bus);

        $date = $request->query('date') ?: now()->toDateString();

        $isToday = Carbon::parse($date)->isSameDay(now());

        $bus->load(['school', 'drivers', 'routes']);

        $students = $bus->students()
            ->with('parent.user')
            ->orderBy('grade')
            ->orderBy('roll_no')
            ->get();

        $attendanceRecords = Attendance::query()
            ->whereDate('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        $attendance = collect(Attendance::trips())
            ->mapWithKeys(fn ($label, $trip) => [
                $trip => $attendanceRecords->where('trip', $trip)->keyBy('student_id'),
            ]);

        $studentStages = $students->map(function (Student $student) use ($attendance) {
            $state = $this->stagesForStudent(
                $attendance[Attendance::TRIP_HOME_TO_SCHOOL][$student->id] ?? null,
                $attendance[Attendance::TRIP_SCHOOL_TO_HOME][$student->id] ?? null,
            );

            return [
                'student' => $student,
                'stages' => $state['stages'],
                'next_action' => $state['next_action'],
                'completed' => $state['completed'],
                'buttons' => $this->stageButtons($state['stages'], $state['next_action']),
            ];
        });

        $allCompleted = $students->isNotEmpty()
            && $studentStages->every(fn ($entry) => $entry['completed']);

        return view('attendance.show', compact('bus', 'studentStages', 'date', 'isToday', 'allCompleted'));
    }

    /**
     * Check a student's next valid attendance action and store it.
     */
    public function mark(Request $request, Bus $bus, Student $student)
    {
        $this->authorizeBus($bus);
        $this->ensureBusActive($bus);

        $validated = $request->validate([
            'action' => ['required', 'in:check_in,check_out'],
            'trip' => ['required', 'in:home_to_school,school_to_home'],
            'date' => ['nullable', 'date'],
        ]);

        if ((int) $student->bus_id !== (int) $bus->id) {
            abort(403, 'This student is not assigned to this bus.');
        }

        $date = ! empty($validated['date']) ? Carbon::parse($validated['date']) : now();

        if ($date->toDateString() !== now()->toDateString()) {
            return back()->withErrors(['date' => 'Attendance can only be marked for today.']);
        }

        $home = Attendance::query()
            ->where('student_id', $student->id)
            ->where('trip', Attendance::TRIP_HOME_TO_SCHOOL)
            ->whereDate('date', $date)
            ->first();

        $school = Attendance::query()
            ->where('student_id', $student->id)
            ->where('trip', Attendance::TRIP_SCHOOL_TO_HOME)
            ->whereDate('date', $date)
            ->first();

        $state = $this->stagesForStudent($home, $school);

        if ($state['completed']) {
            return back()->withErrors(['trip' => "{$student->full_name}'s attendance is already completed for this day."]);
        }

        $expected = $state['next_action'];

        if ($validated['action'] !== $expected['action'] || $validated['trip'] !== $expected['trip']) {
            return back()->withErrors(['trip' => "{$student->full_name}'s next valid action is \"{$expected['label']}\"."]);
        }

        if ($validated['action'] === 'check_in') {
            Attendance::updateOrCreate(
                ['student_id' => $student->id, 'date' => $date, 'trip' => $validated['trip']],
                [
                    'bus_id' => $bus->id,
                    'check_in_at' => now(),
                    'marked_by' => Auth::id(),
                ]
            );

            $message = "{$student->full_name} - {$expected['label']} marked successfully.";
        } else {
            $record = $validated['trip'] === Attendance::TRIP_SCHOOL_TO_HOME ? $school : $home;

            if (! $record || ! $record->isCheckedIn()) {
                return back()->withErrors(['check_out' => "{$student->full_name} must be checked in before checking out."]);
            }

            $record->update([
                'check_out_at' => now(),
                'marked_by' => Auth::id(),
            ]);

            $message = "{$student->full_name} - {$expected['label']} marked successfully.";
        }

        return redirect()
            ->route('attendance.buses.show', ['bus' => $bus, 'date' => $date->toDateString()])
            ->with('success', $message);
    }

    /**
     * Display the attendance history for a bus across dates.
     */
    public function history(Request $request, Bus $bus)
    {
        $this->authorizeBus($bus);

        $bus->load(['school', 'routes', 'drivers']);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = ! empty($validated['from'])
            ? Carbon::parse($validated['from'])
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
            ->paginate(50)
            ->withQueryString();

        $totalRecords = $records->total();

        return view('attendance.history', compact('bus', 'records', 'from', 'to', 'totalRecords'));
    }

    /**
     * Make sure attendance can only be marked on an active bus.
     */
    private function ensureBusActive(Bus $bus): void
    {
        if ($bus->status !== 'Active') {
            abort(403, 'Attendance is only available for active buses.');
        }
    }

    /**
     * Human-readable label for a trip key.
     */
    private function tripLabel(string $trip): string
    {
        return Attendance::trips()[$trip] ?? $trip;
    }

    /**
     * Build the 4-stage daily attendance state for a student and the next valid action.
     *
     * Stages follow the strict sequence: Picked Up from Home -> Dropped at School
     * -> Picked Up from School -> Dropped at Home -> Completed.
     *
     * @return array{
     *     stages: array<int, array{key: string, label: string, done: bool, at: \Illuminate\Support\Carbon|null}>,
     *     next_action: array{action: string, trip: string, label: string}|null,
     *     completed: bool,
     * }
     */
    private function stagesForStudent(?Attendance $home, ?Attendance $school): array
    {
        $pickedUpHome = $home?->isCheckedIn() ?? false;
        $droppedAtSchool = $home?->isCheckedOut() ?? false;
        $pickedUpSchool = $school?->isCheckedIn() ?? false;
        $droppedAtHome = $school?->isCheckedOut() ?? false;

        $stages = [
            ['key' => 'picked_up_home', 'label' => 'Picked Up from Home', 'done' => $pickedUpHome, 'at' => $home?->check_in_at],
            ['key' => 'dropped_at_school', 'label' => 'Dropped at School', 'done' => $droppedAtSchool, 'at' => $home?->check_out_at],
            ['key' => 'picked_up_school', 'label' => 'Picked Up from School', 'done' => $pickedUpSchool, 'at' => $school?->check_in_at],
            ['key' => 'dropped_at_home', 'label' => 'Dropped at Home', 'done' => $droppedAtHome, 'at' => $school?->check_out_at],
        ];

        if (! $pickedUpHome) {
            $nextAction = ['action' => 'check_in', 'trip' => Attendance::TRIP_HOME_TO_SCHOOL, 'label' => 'Pick Up'];
        } elseif (! $droppedAtSchool) {
            $nextAction = ['action' => 'check_out', 'trip' => Attendance::TRIP_HOME_TO_SCHOOL, 'label' => 'Drop at School'];
        } elseif (! $pickedUpSchool) {
            $nextAction = ['action' => 'check_in', 'trip' => Attendance::TRIP_SCHOOL_TO_HOME, 'label' => 'Pick Up from School'];
        } elseif (! $droppedAtHome) {
            $nextAction = ['action' => 'check_out', 'trip' => Attendance::TRIP_SCHOOL_TO_HOME, 'label' => 'Drop at Home'];
        } else {
            $nextAction = null;
        }

        return [
            'stages' => $stages,
            'next_action' => $nextAction,
            'completed' => $nextAction === null,
        ];
    }

    /**
     * Build the queue state for each of the 4 daily stages so the UI can show
     * every button at once: the current stage is active, previous stages are
     * done, and future stages stay locked until their turn arrives.
     *
     * @param  array<int, array{key: string, label: string, done: bool, at: \Illuminate\Support\Carbon|null}>  $stages
     * @param  array{action: string, trip: string, label: string}|null  $nextAction
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     button_label: string,
     *     done: bool,
     *     at: \Illuminate\Support\Carbon|null,
     *     enabled: bool,
     *     action: string,
     *     trip: string,
     * }>
     */
    private function stageButtons(array $stages, ?array $nextAction): array
    {
        $meta = [
            'picked_up_home' => ['action' => 'check_in', 'trip' => Attendance::TRIP_HOME_TO_SCHOOL, 'label' => 'Pick Up'],
            'dropped_at_school' => ['action' => 'check_out', 'trip' => Attendance::TRIP_HOME_TO_SCHOOL, 'label' => 'Drop at School'],
            'picked_up_school' => ['action' => 'check_in', 'trip' => Attendance::TRIP_SCHOOL_TO_HOME, 'label' => 'Pick Up from School'],
            'dropped_at_home' => ['action' => 'check_out', 'trip' => Attendance::TRIP_SCHOOL_TO_HOME, 'label' => 'Drop at Home'],
        ];

        return collect($stages)->map(function (array $stage) use ($nextAction, $meta) {
            $stageMeta = $meta[$stage['key']] ?? ['action' => null, 'trip' => null, 'label' => $stage['label']];

            $enabled = $nextAction
                && $stageMeta['action'] === $nextAction['action']
                && $stageMeta['trip'] === $nextAction['trip'];

            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'button_label' => $stageMeta['label'],
                'done' => $stage['done'],
                'at' => $stage['at'],
                'enabled' => $enabled,
                'action' => $stageMeta['action'],
                'trip' => $stageMeta['trip'],
            ];
        })->values()->all();
    }

    /**
     * Make sure the current user is allowed to access this bus.
     */
    private function authorizeBus(Bus $bus): void
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            return;
        }

        if ($user->hasAnyRole(['School Admin', 'Principal'])) {
            $schoolId = $this->getUserSchoolId($user);

            if ($schoolId && (int) $bus->school_id !== (int) $schoolId) {
                abort(403, 'You are not authorized to access this bus.');
            }

            return;
        }

        if ($user->hasRole('Driver')) {
            $driverId = Driver::where('user_id', $user->id)->value('id');

            if ($driverId && $bus->drivers->contains('id', $driverId)) {
                return;
            }

            abort(403, 'You are not authorized to access this bus.');
        }

        abort(403);
    }

    /**
     * Resolve the school id for school-level admins.
     */
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
