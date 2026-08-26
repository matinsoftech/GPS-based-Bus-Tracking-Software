<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Notifications\TripStartedNotification;
use Illuminate\Console\Command;

class TestTripStartedNotification extends Command
{
    protected $signature = 'gps:test-notification {--trip= : Trip id to notify parents for}';

    protected $description = 'Send a "Trip Started" notification to the parents of students on the trip route';

    public function handle(): int
    {
        $trip = $this->option('trip')
            ? Trip::with(['bus', 'route', 'driver'])->find($this->option('trip'))
            : Trip::with(['bus', 'route', 'driver'])->latest()->first();

        if (! $trip) {
            $this->error('No trip found.');

            return self::FAILURE;
        }

        $notification = new TripStartedNotification($trip);

        $notified = 0;

        foreach ($trip->route->students()->with('parent.user')->get() as $student) {
            $parent = $student->parent?->user;

            if (! $parent) {
                continue;
            }

            $parent->notify($notification);
            $notified++;
        }

        $this->info("Sent Trip Started notification to {$notified} parent(s) for trip #{$trip->id} ({$trip->bus->bus_number} on {$trip->route->name}).");

        return self::SUCCESS;
    }
}
