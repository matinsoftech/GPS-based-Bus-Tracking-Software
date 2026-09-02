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

        $routeLabel = $route->name ?? "({$this->trip->route_id})";
        $busLabel = $bus->bus_number ?? "({$this->trip->bus_id})";

        return [
            'type' => 'trip_ended',
            'title' => 'Trip Ended',
            'message' => 'Bus '.$busLabel.' has completed the '.$this->trip->trip_type_label.' trip on route '.$routeLabel.'.',
            'trip_id' => $this->trip->id,
            'bus_id' => $bus->id ?? $this->trip->bus_id,
            'bus_number' => $bus->bus_number ?? null,
            'route_id' => $route->id ?? $this->trip->route_id,
            'route_name' => $route->name ?? null,
            'trip_type' => $this->trip->trip_type,
            'duration_minutes' => $this->trip->durationInMinutes(),
        ];
    }
}
