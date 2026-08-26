<?php

namespace App\Console\Commands;

use App\Models\Bus;
use App\Models\Student;
use App\Notifications\BusStartedNotification;
use Illuminate\Console\Command;

class TestBusStartedNotification extends Command
{
    protected $signature = 'gps:test-notification {--bus= : Bus id to notify parents for}';

    protected $description = 'Send a "Bus Started" notification to the parents of a bus';

    public function handle(): int
    {
        $bus = $this->option('bus')
            ? Bus::find($this->option('bus'))
            : Bus::first();

        if (! $bus) {
            $this->error('No bus found.');

            return self::FAILURE;
        }

        $routeIds = $bus->routes()->pluck('routes.id');

        $students = Student::whereIn('route_id', $routeIds)
            ->with('parent.user')
            ->get();

        $notified = 0;

        foreach ($students as $student) {
            $parent = $student->parent?->user;

            if (! $parent) {
                continue;
            }

            $parent->notify(new BusStartedNotification($bus));
            $notified++;
        }

        $this->info("Sent Bus Started notification to {$notified} parent(s) for bus {$bus->bus_number}.");

        return self::SUCCESS;
    }
}
