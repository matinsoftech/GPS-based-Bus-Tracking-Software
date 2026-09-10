<?php

namespace App\Traits;

use App\Models\Student;
use App\Models\Trip;
use Illuminate\Notifications\Notification;

trait NotifiesRouteParticipants
{
    /**
     * Notify unique parents and student users for all students on a trip's route.
     *
     * Prevents duplicate notifications when a parent has multiple students
     * assigned to the same route.
     */
    protected function notifyRouteParticipants(Trip $trip, Notification $notification): void
    {
        $students = Student::whereHas('routes', fn ($q) => $q->where('route_id', $trip->route_id))
            ->with('parent.user')
            ->get();

        $students->pluck('parent.user')
            ->filter()
            ->unique('id')
            ->each->notify($notification);

        $students->pluck('user')
            ->filter()
            ->unique('id')
            ->each->notify($notification);
    }
}
