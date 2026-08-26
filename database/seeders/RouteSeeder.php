<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Sample routes with Nepal-related data.
     *
     * @var array<int, array<string, mixed>>
     */
    private const ROUTES = [
        [
            'name' => 'Baneshwor Shuttle',
            'route_code' => 'GVHS-R1',
            'start_location' => 'Baneshwor Chowk',
            'end_location' => 'Green Valley High School, Naxal',
            'estimated_distance' => 8.50,
            'estimated_duration' => 35,
            'is_active' => true,
            'driver' => 'DRV-001',
            'buses' => ['BUS-001'],
            'stops' => [
                ['name' => 'Baneshwor Chowk', 'latitude' => 27.6993300, 'longitude' => 85.3392200, 'stop_order' => 1, 'pickup_time' => '07:00:00', 'drop_time' => '15:10:00'],
                ['name' => 'New Baneshwor', 'latitude' => 27.6958000, 'longitude' => 85.3348000, 'stop_order' => 2, 'pickup_time' => '07:05:00', 'drop_time' => '15:15:00'],
                ['name' => 'Minbhawan', 'latitude' => 27.6907000, 'longitude' => 85.3275000, 'stop_order' => 3, 'pickup_time' => '07:10:00', 'drop_time' => '15:20:00'],
                ['name' => 'Koteshwor', 'latitude' => 27.6777000, 'longitude' => 85.3408000, 'stop_order' => 4, 'pickup_time' => '07:15:00', 'drop_time' => '15:25:00'],
                ['name' => 'Green Valley High School, Naxal', 'latitude' => 27.7151000, 'longitude' => 85.3228000, 'stop_order' => 5, 'pickup_time' => '07:30:00', 'drop_time' => '15:30:00'],
            ],
        ],
        [
            'name' => 'Kalanki Express',
            'route_code' => 'GVHS-R2',
            'start_location' => 'Kalanki Bus Stop',
            'end_location' => 'Green Valley High School, Naxal',
            'estimated_distance' => 12.00,
            'estimated_duration' => 50,
            'is_active' => true,
            'driver' => 'DRV-004',
            'buses' => ['BUS-002'],
            'stops' => [
                ['name' => 'Kalanki Bus Stop', 'latitude' => 27.6999000, 'longitude' => 85.2812000, 'stop_order' => 1, 'pickup_time' => '06:45:00', 'drop_time' => '15:15:00'],
                ['name' => 'Kalimati', 'latitude' => 27.7008000, 'longitude' => 85.3017000, 'stop_order' => 2, 'pickup_time' => '06:55:00', 'drop_time' => '15:25:00'],
                ['name' => 'Tripureshwor', 'latitude' => 27.7055000, 'longitude' => 85.3094000, 'stop_order' => 3, 'pickup_time' => '07:05:00', 'drop_time' => '15:35:00'],
                ['name' => 'Thamel', 'latitude' => 27.7161000, 'longitude' => 85.3126000, 'stop_order' => 4, 'pickup_time' => '07:15:00', 'drop_time' => '15:45:00'],
                ['name' => 'Green Valley High School, Naxal', 'latitude' => 27.7151000, 'longitude' => 85.3228000, 'stop_order' => 5, 'pickup_time' => '07:30:00', 'drop_time' => '16:00:00'],
            ],
        ],
        [
            'name' => 'Gwarko Morning Loop',
            'route_code' => 'SA-R1',
            'start_location' => 'Gwarko Chowk',
            'end_location' => 'Sunrise Academy, Jawalakhel',
            'estimated_distance' => 6.00,
            'estimated_duration' => 25,
            'is_active' => true,
            'driver' => 'DRV-005',
            'buses' => ['BUS-003'],
            'stops' => [
                ['name' => 'Gwarko Chowk', 'latitude' => 27.6723000, 'longitude' => 85.3183000, 'stop_order' => 1, 'pickup_time' => '07:05:00', 'drop_time' => '15:05:00'],
                ['name' => 'Lagankhel', 'latitude' => 27.6670000, 'longitude' => 85.3236000, 'stop_order' => 2, 'pickup_time' => '07:10:00', 'drop_time' => '15:10:00'],
                ['name' => 'Pulchowk', 'latitude' => 27.6806000, 'longitude' => 85.3179000, 'stop_order' => 3, 'pickup_time' => '07:15:00', 'drop_time' => '15:15:00'],
                ['name' => 'Kupondole', 'latitude' => 27.6903000, 'longitude' => 85.3128000, 'stop_order' => 4, 'pickup_time' => '07:20:00', 'drop_time' => '15:20:00'],
                ['name' => 'Sunrise Academy, Jawalakhel', 'latitude' => 27.6612000, 'longitude' => 85.3159000, 'stop_order' => 5, 'pickup_time' => '07:30:00', 'drop_time' => '15:30:00'],
            ],
        ],
        [
            'name' => 'Jhamsikhel Route',
            'route_code' => 'SA-R2',
            'start_location' => 'Jhamsikhel',
            'end_location' => 'Sunrise Academy, Jawalakhel',
            'estimated_distance' => 5.50,
            'estimated_duration' => 22,
            'is_active' => true,
            'driver' => 'DRV-002',
            'buses' => ['BUS-005'],
            'stops' => [
                ['name' => 'Jhamsikhel', 'latitude' => 27.6721000, 'longitude' => 85.2990000, 'stop_order' => 1, 'pickup_time' => '07:10:00', 'drop_time' => '15:05:00'],
                ['name' => 'Sanepa', 'latitude' => 27.6765000, 'longitude' => 85.3065000, 'stop_order' => 2, 'pickup_time' => '07:15:00', 'drop_time' => '15:10:00'],
                ['name' => 'Kusunti', 'latitude' => 27.6672000, 'longitude' => 85.3119000, 'stop_order' => 3, 'pickup_time' => '07:20:00', 'drop_time' => '15:15:00'],
                ['name' => 'Sunrise Academy, Jawalakhel', 'latitude' => 27.6612000, 'longitude' => 85.3159000, 'stop_order' => 4, 'pickup_time' => '07:30:00', 'drop_time' => '15:25:00'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::orderBy('id')->get();

        if ($schools->isEmpty()) {
            $this->command->error('No school found. Run SchoolSeeder before RouteSeeder.');

            return;
        }

        DB::transaction(function () use ($schools) {
            foreach (self::ROUTES as $index => $data) {
                $stops = $data['stops'];

                $driverEmployeeId = $data['driver'];

                $busNumbers = $data['buses'];

                unset($data['stops'], $data['driver'], $data['buses']);

                $school = $schools->get($index % $schools->count());

                $driver = Driver::where('employee_id', $driverEmployeeId)->first();

                $route = Route::updateOrCreate(
                    ['route_code' => $data['route_code']],
                    array_merge($data, [
                        'school_id' => $school->id,
                    ])
                );

                if ($driver) {
                    $route->drivers()->syncWithoutDetaching([$driver->id]);
                }

                Bus::whereIn('bus_number', $busNumbers)->each(function ($bus) use ($driver) {
                    if ($driver) {
                        $bus->drivers()->syncWithoutDetaching([$driver->id]);
                    }
                });

                foreach ($stops as $stop) {
                    RouteStop::updateOrCreate(
                        ['route_id' => $route->id, 'stop_order' => $stop['stop_order']],
                        $stop
                    );
                }
            }
        });

        $this->command->info('Routes seeded successfully.');
    }
}
