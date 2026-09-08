<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $this->call([
            PermissionSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        |
        | Roles depend on permissions, so this comes second.
        |
        */

        $this->call([
            RoleSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        |
        | Users depend on roles, so this comes last.
        |
        */
        $this->call([

            SchoolSeeder::class,

        ]);

        $this->call([
            UserSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Transport
        |--------------------------------------------------------------------------
        |
        | Drivers, buses and routes depend on schools and users.
        |
        */
        $this->call([
            DriverSeeder::class,
            BusSeeder::class,
            GpsDeviceSeeder::class,
            BusLocationSeeder::class,
            RouteSeeder::class,
            StudentSeeder::class,
            PlanSeeder::class,
        ]);
    }
}
