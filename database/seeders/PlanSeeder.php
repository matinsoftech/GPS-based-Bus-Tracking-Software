<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Basic',
            'monthly_price' => 999,
            'yearly_price' => 9999,

            'max_buses' => 5,
            'max_students' => 100,
            'max_parents' => 100,
            'max_drivers' => 5,
            'max_routes' => 5,
            'max_devices' => 5,

            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => false,
                'reports' => false,
                'analytics' => false,
            ],

            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Standard',
            'monthly_price' => 1999,
            'yearly_price' => 19999,

            'max_buses' => 20,
            'max_students' => 500,
            'max_parents' => 500,
            'max_drivers' => 25,
            'max_routes' => 20,
            'max_devices' => 20,

            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => true,
                'reports' => true,
                'analytics' => false,
            ],

            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Premium',
            'monthly_price' => 3999,
            'yearly_price' => 39999,

            'max_buses' => 50,
            'max_students' => 1500,
            'max_parents' => 1500,
            'max_drivers' => 60,
            'max_routes' => 50,
            'max_devices' => 50,

            'features' => [
                'live_tracking' => true,
                'parent_app' => true,
                'notifications' => true,
                'attendance' => true,
                'reports' => true,
                'analytics' => true,
            ],

            'is_active' => true,
        ]);
    }
}