<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * CRUD actions applied to resource modules.
     */
    private const CRUD = [
        'create',
        'view',
        'update',
        'delete',
    ];

    /**
     * Module map.
     *
     * This is the single source of truth for
     * all permissions in the application.
     */
    public const MODULES = [

        // Dashboard
        'dashboard' => [
            'view',
        ],

        // School
        'school' => self::CRUD,

        // School administrators/users
        'school-admin' => self::CRUD,

        // Drivers
        'driver' => self::CRUD,

        // Parents
        'parent' => self::CRUD,

        // Students
        'student' => self::CRUD,

        // Buses
        'bus' => self::CRUD,

        // Routes
        'route' => self::CRUD,

        // Stops
        'stop' => self::CRUD,

        // Bus assignments
        'bus-assignment' => self::CRUD,

        // Student assignments
        'student-assignment' => self::CRUD,

        // Trips
        'trip' => [
            'create',
            'view',
            'update',
            'delete',
            'start',
            'end',
        ],

        // GPS
        'gps' => [
            'view',
            'track',
        ],

        // Attendance
        'attendance' => [
            'view',
            'mark',
            'update',
        ],

        // Pickup
        'pickup' => [
            'view',
            'mark',
        ],

        // Drop
        'drop' => [
            'view',
            'mark',
        ],

        // Notifications
        'notification' => [
            'view',
            'send',
        ],

        // Reports
        'report' => [
            'view',
            'export',
        ],

        // Emergency
        'emergency' => [
            'view',
            'create',
            'resolve',
        ],

        // Profile
        'profile' => [
            'view',
            'update',
        ],

        // Roles
        'role' => self::CRUD,

        // Permissions
        'permission' => self::CRUD,

        // Settings
        'settings' => [
            'view',
            'update',
        ],

        // Plans
        'plan' => self::CRUD,

        // Subscriptions
        'subscription' => self::CRUD,
    ];

    /**
     * Convert module definitions into:
     *
     * module.action
     *
     * Example:
     *
     * student.view
     * student.create
     * student.update
     * student.delete
     */
    public static function names(array $excludeModules = []): array
    {
        $names = [];

        foreach (self::MODULES as $module => $actions) {

            if (in_array($module, $excludeModules, true)) {
                continue;
            }

            foreach ($actions as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return $names;
    }

    /**
     * Seed permissions.
     */
    public function run(): void
    {
        // Clear Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::names() as $name) {

            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Clear cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
