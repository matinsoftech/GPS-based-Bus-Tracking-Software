<?php

namespace App\Notifications;

use App\Models\ProblemReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProblemReportedNotification extends Notification
{
    use Queueable;

    public function __construct(public ProblemReport $problemReport) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $report = $this->problemReport;
        $reporter = $report->reporter;

        return [
            'type' => 'problem_reported',
            'title' => 'Problem Reported',
            'message' => ($reporter?->name ?? 'A user').' reported a '
                .$report->severity.' severity '.$report->category.' problem.',
            'problem_report_id' => $report->id,
            'school_id' => $report->school_id,
            'category' => $report->category,
            'severity' => $report->severity,
            'status' => $report->status,
            'reporter_user_id' => $report->reporter_user_id,
            'reporter_name' => $reporter?->name,
        ];
    }
}
