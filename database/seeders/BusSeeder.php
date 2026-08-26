<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Sample buses with Nepal-related data.
     *
     * @var array<int, array<string, mixed>>
     */
    private const BUSES = [
        [
            'bus_number' => 'BUS-001',
            'registration_number' => 'BA 1 KHA 1234',
            'make' => 'Ashok Leyland',
            'model' => 'Viking',
            'year' => 2021,
            'capacity' => 41,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1001',
            'insurance_number' => 'INS-1001',
            'insurance_expiry_date' => '2027-06-30',
            'last_service_date' => '2026-07-15',
            'status' => 'Active',
            'notes' => 'Used on the Baneshwor shuttle route.',
            'driver' => 'DRV-009',
        ],
        [
            'bus_number' => 'BUS-002',
            'registration_number' => 'BA 1 YA 5678',
            'make' => 'Tata',
            'model' => 'LPO 1613',
            'year' => 2020,
            'capacity' => 45,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1002',
            'insurance_number' => 'INS-1002',
            'insurance_expiry_date' => '2027-03-15',
            'last_service_date' => '2026-06-20',
            'status' => 'Active',
            'notes' => 'Backup bus for the Kalanki express.',
            'driver' => 'DRV-003',
        ],
        [
            'bus_number' => 'BUS-003',
            'registration_number' => 'BA 2 PA 9012',
            'make' => 'VDL',
            'model' => 'Citea',
            'year' => 2022,
            'capacity' => 50,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1003',
            'insurance_number' => 'INS-1003',
            'insurance_expiry_date' => '2027-09-01',
            'last_service_date' => '2026-05-10',
            'status' => 'Active',
            'notes' => 'Main bus for the Gwarko morning loop.',
            'driver' => 'DRV-003',
        ],
        [
            'bus_number' => 'BUS-004',
            'registration_number' => 'BA 3 BA 3456',
            'make' => 'Toyota',
            'model' => 'Coaster',
            'year' => 2019,
            'capacity' => 30,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1004',
            'insurance_number' => 'INS-1004',
            'insurance_expiry_date' => '2026-12-20',
            'last_service_date' => '2026-04-25',
            'status' => 'Maintenance',
            'notes' => 'Small shuttle for special events.',
            'driver' => 'DRV-006',
        ],
        [
            'bus_number' => 'BUS-005',
            'registration_number' => 'BA 1 CHA 7890',
            'make' => 'Higer',
            'model' => 'H7',
            'year' => 2023,
            'capacity' => 40,
            'fuel_type' => 'Electric',
            'gps_device_id' => 'GPS-1005',
            'insurance_number' => 'INS-1005',
            'insurance_expiry_date' => '2028-01-10',
            'last_service_date' => '2026-07-01',
            'status' => 'Active',
            'notes' => 'Electric bus on the Jhamsikhel route.',
            'driver' => 'DRV-002',
        ],
        [
            'bus_number' => 'BUS-006',
            'registration_number' => 'BA 1 KA 2468',
            'make' => 'Ashok Leyland',
            'model' => 'Panther',
            'year' => 2018,
            'capacity' => 60,
            'fuel_type' => 'Diesel',
            'gps_device_id' => 'GPS-1006',
            'insurance_number' => 'INS-1006',
            'insurance_expiry_date' => '2026-08-05',
            'last_service_date' => '2026-02-18',
            'status' => 'Inactive',
            'notes' => 'Retired from daily service.',
            'driver' => 'DRV-007',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::where('email', 'superadmin@example.com')->first() ?? User::first();

        if (! $creator) {
            $this->command->error('No user found. Run UserSeeder before BusSeeder.');

            return;
        }

        $schools = School::orderBy('id')->get();

        if ($schools->isEmpty()) {
            $this->command->error('No school found. Run SchoolSeeder before BusSeeder.');

            return;
        }

        DB::transaction(function () use ($creator, $schools) {
            foreach (self::BUSES as $index => $data) {
                $driverEmployeeId = $data['driver'];

                unset($data['driver']);

                $school = $schools->get($index % $schools->count());

                $driverId = Driver::where('employee_id', $driverEmployeeId)->value('id');

                $bus = Bus::updateOrCreate(
                    ['bus_number' => $data['bus_number']],
                    array_merge($data, [
                        'school_id' => $school->id,
                        'created_by' => $creator->id,
                    ])
                );

                if ($driverId) {
                    $bus->drivers()->syncWithoutDetaching([$driverId]);
                }
            }
        });

        $this->command->info('Buses seeded successfully.');
    }
}
