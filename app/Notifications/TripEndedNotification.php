<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TripEndedNotification extends Notification
{
    use Queueable;

    public function __construct(public Trip $trip) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $route = $this->trip->route;
        $bus = $this->trip->bus;

        return [
            'type' => 'trip_ended',
            'title' => 'Trip Ended',
            'message' => 'Bus '.$bus->bus_number.' has completed the '.$this->trip->trip_type_label.' trip on route '.$route->name.'.',
            'trip_id' => $this->trip->id,
            'bus_id' => $bus->id,
            'bus_number' => $bus->bus_number,
            'route_id' => $route->id,
            'route_name' => $route->name,
            'trip_type' => $this->trip->trip_type,
            'duration_minutes' => $this->trip->durationInMinutes(),
        ];
    }
}