<?php

namespace App\Notifications;

use App\Models\RouteStop;
use App\Models\Trip;
use App\Models\TripStopArrival;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BusStopArrivalNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Trip $trip,
        public TripStopArrival $arrival,
        public RouteStop $stop,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $bus = $this->trip->bus;

        $punctuality = $this->arrival->punctuality;
        $punctualityLabel = match ($punctuality) {
            'early' => 'early',
            'late' => 'late',
            default => 'on time',
        };

        return [
            'type' => 'bus_stop_arrival',
            'title' => 'Bus Arrived at Stop',
            'message' => 'Bus '.$bus->bus_number.' has arrived at '.$this->stop->name.' ('.$punctualityLabel.').',
            'trip_id' => $this->trip->id,
            'trip_type' => $this->trip->trip_type,
            'bus_id' => $bus->id,
            'bus_number' => $bus->bus_number,
            'route_id' => $this->trip->route_id,
            'route_stop_id' => $this->stop->id,
            'stop_name' => $this->stop->name,
            'stop_order' => $this->stop->stop_order,
            'arrived_at' => $this->arrival->arrived_at?->toIso8601String(),
            'punctuality' => $punctuality,
        ];
    }
}
