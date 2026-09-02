<?php

namespace App\Services;

use App\Models\Student;
use App\Notifications\StudentAttendanceNotification;
use Illuminate\Support\Facades\Log;

class AttendanceNotificationService
{
    public const ACTIONS = [
        'picked_up_home',
        'dropped_at_school',
        'picked_up_school',
        'dropped_at_home',
    ];

    public function notifyParent(Student $student, string $actionKey, ?string $markedAt = null): void
    {
        if (! in_array($actionKey, self::ACTIONS, true)) {
            return;
        }

        try {
            $student->loadMissing('parent.user', 'route');

            $parent = $student->parent?->user;

            if (! $parent) {
                return;
            }

            $parent->notify(new StudentAttendanceNotification($student, $actionKey, $markedAt));
        } catch (\Throwable $e) {
            Log::error('Attendance parent notification failed', [
                'student_id' => $student->id,
                'action' => $actionKey,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
