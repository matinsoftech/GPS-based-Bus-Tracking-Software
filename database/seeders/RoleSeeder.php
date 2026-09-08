<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Modules that School Admin cannot manage.
     */
    private const SCHOOL_ADMIN_EXCLUDED_MODULES = [
        'school-admin',
        'role',
        'permission',
        'plan',
    ];

    /**
     * Driver permissions.
     */
    private const DRIVER_PERMISSIONS = [

        'dashboard.view',

        // Profile
        'profile.view',
        'profile.update',

        // Trips
        'trip.view',
        'trip.start',
        'trip.end',

        // GPS
        'gps.view',
        'gps.track',

        // Attendance
        'attendance.view',
        'attendance.mark',

        // Pickup
        'pickup.view',
        'pickup.mark',

        // Drop
        'drop.view',
        'drop.mark',

        // Notifications
        'notification.view',

        // Emergency
        'emergency.view',
        'emergency.create',
    ];

    /**
     * Parent permissions.
     */
    private const PARENT_PERMISSIONS = [

        'dashboard.view',

        // Profile
        'profile.view',
        'profile.update',

        // Children
        'student.view',

        // Transport
        'bus.view',
        'route.view',
        'stop.view',
        'trip.view',

        // GPS
        'gps.view',

        // Attendance
        'attendance.view',

        // Pickup / Drop
        'pickup.view',
        'drop.view',

        // Notifications
        'notification.view',

        // Reports
        'report.view',
    ];

    /**
     * Seed roles and permissions.
     */
    public function run(): void
    {
        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        // Super Admin gets every permission.
        $superAdmin->syncPermissions(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | SCHOOL ADMIN / PRINCIPAL
        |--------------------------------------------------------------------------
        */

        $schoolAdmin = Role::firstOrCreate([
            'name' => 'School Admin',
            'guard_name' => 'web',
        ]);

        /*
         * School Admin gets all school-management permissions,
         * except:
         *
         * school-admin.*
         * role.*
         * permission.*
         */

        $schoolAdmin->syncPermissions(
            PermissionSeeder::names(
                self::SCHOOL_ADMIN_EXCLUDED_MODULES
            )
        );

        /*
        |--------------------------------------------------------------------------
        | DRIVER
        |--------------------------------------------------------------------------
        */

        $driver = Role::firstOrCreate([
            'name' => 'Driver',
            'guard_name' => 'web',
        ]);

        $driver->syncPermissions(
            self::DRIVER_PERMISSIONS
        );

        /*
        |--------------------------------------------------------------------------
        | PARENT
        |--------------------------------------------------------------------------
        */

        $parent = Role::firstOrCreate([
            'name' => 'Parent',
            'guard_name' => 'web',
        ]);

        $parent->syncPermissions(
            self::PARENT_PERMISSIONS
        );

        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
