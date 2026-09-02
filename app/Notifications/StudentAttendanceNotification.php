<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentAttendanceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Student $student,
        public string $actionKey,
        public ?string $markedAt = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->stageLabel($this->actionKey);

        $title = str_starts_with($this->actionKey, 'picked')
            ? 'Child Picked Up'
            : 'Child Dropped Off';

        return [
            'type' => 'attendance',
            'title' => $title,
            'message' => 'Your child '.$this->student->full_name.' was '.$label.'.',
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'route_id' => $this->student->route_id,
            'route_name' => $this->student->route?->name,
            'action' => $this->actionKey,
            'action_label' => ucfirst($this->stageLabel($this->actionKey)),
            'trip' => $this->tripForAction($this->actionKey),
            'marked_at' => $this->markedAt,
            'url' => $this->student->route_id
                ? route('parent.student.attendance', $this->student)
                : null,
        ];
    }

    private function stageLabel(string $actionKey): string
    {
        return match ($actionKey) {
            'picked_up_home' => 'picked up from home',
            'dropped_at_school' => 'dropped at school',
            'picked_up_school' => 'picked up from school',
            'dropped_at_home' => 'dropped at home',
            default => 'checked in',
        };
    }

    private function tripForAction(string $actionKey): string
    {
        return in_array($actionKey, ['picked_up_home', 'dropped_at_school'], true)
            ? 'home_to_school'
            : 'school_to_home';
    }
}
