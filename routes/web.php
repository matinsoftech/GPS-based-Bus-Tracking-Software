<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\BusLocationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\DriverTripWebController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentProfileController;
use App\Http\Controllers\PrincipalDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RouteStopController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $user = auth()->user();

    if ($user?->hasRole('Super Admin')) {
        return redirect()->route('dashboard');
    }

    if ($user?->hasRole('School Admin')) {
        return redirect()->route('principal.dashboard');
    }

    if ($user?->hasRole('Driver')) {
        return redirect()->route('driver.dashboard');
    }

    if ($user?->hasRole('Parent')) {
        return redirect()->route('parent.dashboard');
    }

    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Super Admin Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/systemadmin/dashboard', [SuperAdminDashboardController::class, 'index'])
        ->middleware([
            'verified',
            'permission:dashboard.view',
            'role:Super Admin',
        ])
        ->name('dashboard');

    Route::get('/systemadmin/dashboard/fleet-data', [SuperAdminDashboardController::class, 'fleetData'])
        ->middleware([
            'verified',
            'permission:dashboard.view',
            'role:Super Admin',
        ])
        ->name('dashboard.fleet-data');

    /*
    |--------------------------------------------------------------------------
    | Vehicle Tracking (NazarTrack All Vehicles)
    |--------------------------------------------------------------------------
    */

    Route::get('/super-admin/vehicle-tracking', [VehicleTrackingController::class, 'index'])
        ->middleware([
            'verified',
            'role:Super Admin',
        ])
        ->name('vehicle-tracking');

    Route::get('/super-admin/vehicle-tracking/data', [VehicleTrackingController::class, 'data'])
        ->middleware([
            'verified',
            'role:Super Admin',
        ])
        ->name('vehicle-tracking.data');

    /*
    |--------------------------------------------------------------------------
    | Principal Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/principal/dashboard', [PrincipalDashboardController::class, 'index'])
        ->middleware([
            'permission:dashboard.view',
            'role:School Admin', // user role name like this: school-admin
        ])
        ->name('principal.dashboard');

    Route::get('/principal/dashboard/fleet-data', [PrincipalDashboardController::class, 'fleetData'])
        ->middleware([
            'permission:dashboard.view',
            'role:School Admin',
        ])
        ->name('principal.dashboard.fleet-data');

    Route::prefix('principal')->name('principal.')->middleware(['auth', 'role:School Admin'])->group(function () {
        Route::get('/trips', [PrincipalDashboardController::class, 'tripsIndex'])
            ->middleware('permission:trip.view')
            ->name('trips.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Driver Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/driver/dashboard', [DriverDashboardController::class, 'index'])
        ->middleware([
            'permission:dashboard.view',
            'role:Driver',
        ])
        ->name('driver.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Driver Live Tracking
    |--------------------------------------------------------------------------
    */

    Route::get('/driver/live-tracking', [DriverDashboardController::class, 'liveTracking'])
        ->middleware([
            'permission:dashboard.view',
            'role:Driver',
        ])
        ->name('driver.live-tracking');

    /*
    |--------------------------------------------------------------------------
    | Driver Trips (Web)
    |--------------------------------------------------------------------------
    */

    Route::prefix('driver')->name('driver.')->middleware(['auth', 'role:Driver'])->group(function () {
        Route::get('/trips', [DriverTripWebController::class, 'index'])
            ->middleware('permission:trip.view')
            ->name('trips.index');

        Route::get('/trips/start', [DriverTripWebController::class, 'create'])
            ->middleware('permission:trip.start')
            ->name('trips.create');

        Route::post('/trips/toggle', [DriverTripWebController::class, 'toggle'])
            ->middleware('permission:trip.start')
            ->name('trips.toggle');
    });

    /*
    |--------------------------------------------------------------------------
    | Parent Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])
        ->middleware([
            'permission:dashboard.view',
            'role:Parent',
        ])
        ->name('parent.dashboard');

    Route::get('/parent/children', [ParentDashboardController::class, 'children'])
        ->middleware([
            'permission:student.view',
            'role:Parent',
        ])
        ->name('parent.children');

    Route::get('/parent/students/{student}/attendance', [ParentDashboardController::class, 'studentAttendance'])
        ->middleware([
            'permission:student.view',
            'role:Parent',
        ])
        ->name('parent.student.attendance');

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    */

    Route::get('/drivers', [DriverController::class, 'index'])
        ->middleware('permission:driver.view')
        ->name('drivers.index');

    Route::get('/drivers/create', [DriverController::class, 'create'])
        ->middleware('permission:driver.create')
        ->name('drivers.create');

    Route::post('/drivers', [DriverController::class, 'store'])
        ->middleware('permission:driver.create')
        ->name('drivers.store');

    Route::get('/drivers/{driver}', [DriverController::class, 'show'])
        ->middleware('permission:driver.view')
        ->name('drivers.show');

    Route::get('/drivers/{driver}/edit', [DriverController::class, 'edit'])
        ->middleware('permission:driver.update')
        ->name('drivers.edit');

    Route::put('/drivers/{driver}', [DriverController::class, 'update'])
        ->middleware('permission:driver.update')
        ->name('drivers.update');

    Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])
        ->middleware('permission:driver.delete')
        ->name('drivers.destroy');

    /*
    |--------------------------------------------------------------------------
    | Schools
    |--------------------------------------------------------------------------
    */

    Route::get('/schools', [SchoolController::class, 'index'])
        ->middleware('permission:school.view')
        ->name('schools.index');

    Route::get('/schools/create', [SchoolController::class, 'create'])
        ->middleware('permission:school.create')
        ->name('schools.create');

    Route::post('/schools', [SchoolController::class, 'store'])
        ->middleware('permission:school.create')
        ->name('schools.store');

    Route::get('/schools/{school}', [SchoolController::class, 'show'])
        ->middleware('permission:school.view')
        ->name('schools.show');

    Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])
        ->middleware('permission:school.update')
        ->name('schools.edit');

    Route::put('/schools/{school}', [SchoolController::class, 'update'])
        ->middleware('permission:school.update')
        ->name('schools.update');

    Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])
        ->middleware('permission:school.delete')
        ->name('schools.destroy');

    /*
    |--------------------------------------------------------------------------
    | Parents
    |--------------------------------------------------------------------------
    */

    Route::get('/parents', [ParentProfileController::class, 'index'])
        ->middleware('permission:parent.view')
        ->name('parents.index');

    Route::get('/parents/create', [ParentProfileController::class, 'create'])
        ->middleware('permission:parent.create')
        ->name('parents.create');

    Route::post('/parents', [ParentProfileController::class, 'store'])
        ->middleware('permission:parent.create')
        ->name('parents.store');

    Route::get('/parents/{parentProfile}', [ParentProfileController::class, 'show'])
        ->middleware('permission:parent.view')
        ->name('parents.show');

    Route::get('/parents/{parentProfile}/edit', [ParentProfileController::class, 'edit'])
        ->middleware('permission:parent.update')
        ->name('parents.edit');

    Route::put('/parents/{parentProfile}', [ParentProfileController::class, 'update'])
        ->middleware('permission:parent.update')
        ->name('parents.update');

    Route::delete('/parents/{parentProfile}', [ParentProfileController::class, 'destroy'])
        ->middleware('permission:parent.delete')
        ->name('parents.destroy');

    /*
    |--------------------------------------------------------------------------
    | School Admins
    |--------------------------------------------------------------------------
    */

    Route::get('/school-admins', [SchoolAdminController::class, 'index'])
        ->middleware('permission:school-admin.view')
        ->name('school-admins.index');

    Route::get('/school-admins/create', [SchoolAdminController::class, 'create'])
        ->middleware('permission:school-admin.create')
        ->name('school-admins.create');

    Route::post('/school-admins', [SchoolAdminController::class, 'store'])
        ->middleware('permission:school-admin.create')
        ->name('school-admins.store');

    Route::get('/school-admins/{schoolAdmin}', [SchoolAdminController::class, 'show'])
        ->middleware('permission:school-admin.view')
        ->name('school-admins.show');

    Route::get('/school-admins/{schoolAdmin}/edit', [SchoolAdminController::class, 'edit'])
        ->middleware('permission:school-admin.update')
        ->name('school-admins.edit');

    Route::put('/school-admins/{schoolAdmin}', [SchoolAdminController::class, 'update'])
        ->middleware('permission:school-admin.update')
        ->name('school-admins.update');

    Route::delete('/school-admins/{schoolAdmin}', [SchoolAdminController::class, 'destroy'])
        ->middleware('permission:school-admin.delete')
        ->name('school-admins.destroy');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    |
    | Super Admin only: every route is protected by the `manage-users` gate.
    | Any other authenticated user who manually enters one of these URLs
    | receives a 403 response.
    |
    */

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('can:manage-users')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('can:manage-users')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('can:manage-users')
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('can:manage-users')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('can:manage-users')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:manage-users')
        ->name('users.destroy');

    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */

    Route::get('/students', [StudentController::class, 'index'])
        ->middleware('permission:student.view')
        ->name('students.index');

    Route::get('/students/create', [StudentController::class, 'create'])
        ->middleware('permission:student.create')
        ->name('students.create');

    Route::post('/students', [StudentController::class, 'store'])
        ->middleware('permission:student.create')
        ->name('students.store');

    Route::get('/students/{student}', [StudentController::class, 'show'])
        ->middleware('permission:student.view')
        ->name('students.show');

    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])
        ->middleware('permission:student.update')
        ->name('students.edit');

    Route::put('/students/{student}', [StudentController::class, 'update'])
        ->middleware('permission:student.update')
        ->name('students.update');

    Route::delete('/students/{student}', [StudentController::class, 'destroy'])
        ->middleware('permission:student.delete')
        ->name('students.destroy');

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/routes', [RouteController::class, 'index'])
        ->middleware('permission:route.view')
        ->name('routes.index');

    Route::get('/routes/create', [RouteController::class, 'create'])
        ->middleware('permission:route.create')
        ->name('routes.create');

    Route::post('/routes', [RouteController::class, 'store'])
        ->middleware('permission:route.create')
        ->name('routes.store');

    Route::get('/routes/{route}', [RouteController::class, 'show'])
        ->middleware('permission:route.view')
        ->name('routes.show');

    Route::get('/routes/{route}/edit', [RouteController::class, 'edit'])
        ->middleware('permission:route.update')
        ->name('routes.edit');

    Route::put('/routes/{route}', [RouteController::class, 'update'])
        ->middleware('permission:route.update')
        ->name('routes.update');

    Route::delete('/routes/{route}', [RouteController::class, 'destroy'])
        ->middleware('permission:route.delete')
        ->name('routes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Route Stops
    |--------------------------------------------------------------------------
    */

    Route::post('/routes/{route}/stops', [RouteStopController::class, 'store'])
        ->middleware('permission:route.update')
        ->name('routes.stops.store');

    Route::put('/route-stops/{stop}', [RouteStopController::class, 'update'])
        ->middleware('permission:route.update')
        ->name('route-stops.update');

    Route::delete('/route-stops/{stop}', [RouteStopController::class, 'destroy'])
        ->middleware('permission:route.update')
        ->name('route-stops.destroy');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission:profile.view')
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:profile.update')
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('permission:profile.update')
        ->name('profile.destroy');

    Route::get('/buses', [BusController::class, 'index'])
        ->middleware('permission:bus.view')
        ->name('buses.index');

    Route::get('/buses/create', [BusController::class, 'create'])
        ->middleware('permission:bus.create')
        ->name('buses.create');

    Route::post('/buses', [BusController::class, 'store'])
        ->middleware('permission:bus.create')
        ->name('buses.store');

    Route::get('/buses/{bus}', [BusController::class, 'show'])
        ->middleware('permission:bus.view')
        ->name('buses.show');

    Route::get('/buses/{bus}/edit', [BusController::class, 'edit'])
        ->middleware('permission:bus.update')
        ->name('buses.edit');

    Route::put('/buses/{bus}', [BusController::class, 'update'])
        ->middleware('permission:bus.update')
        ->name('buses.update');

    Route::delete('/buses/{bus}', [BusController::class, 'destroy'])
        ->middleware('permission:bus.delete')
        ->name('buses.destroy');

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware([
            'permission:attendance.view',
            'role:Super Admin|School Admin|Driver',
        ])
        ->name('attendance.index');

    Route::get('/attendance/routes/{route}', [AttendanceController::class, 'show'])
        ->middleware([
            'permission:attendance.view',
            'role:Super Admin|School Admin|Driver',
        ])
        ->name('attendance.routes.show');

    Route::get('/attendance/routes/{route}/history', [AttendanceController::class, 'history'])
        ->middleware([
            'permission:attendance.view',
            'role:Super Admin|School Admin|Driver',
        ])
        ->name('attendance.routes.history');

    Route::post('/attendance/routes/{route}/students/{student}', [AttendanceController::class, 'mark'])
        ->middleware([
            'permission:attendance.mark',
            'role:Super Admin|School Admin|Driver',
        ])
        ->name('attendance.mark');

    Route::get('/bus-location', [BusLocationController::class, 'index'])
        ->middleware('role:Super Admin|School Admin|Driver|Parent')
        ->name('bus_location');

    Route::get('/bus-location/latest', [BusLocationController::class, 'latestJson'])
        ->middleware('role:Super Admin|School Admin|Driver|Parent')
        ->name('bus_location.latest');

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions (Super Admin only)
    |--------------------------------------------------------------------------
    */

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware(['role:Super Admin', 'permission:role.view'])
        ->name('roles.index');

    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware(['role:Super Admin', 'permission:role.create'])
        ->name('roles.create');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware(['role:Super Admin', 'permission:role.create'])
        ->name('roles.store');

    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->middleware(['role:Super Admin', 'permission:role.view'])
        ->name('roles.show');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware(['role:Super Admin', 'permission:role.update'])
        ->name('roles.edit');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware(['role:Super Admin', 'permission:role.update'])
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware(['role:Super Admin', 'permission:role.delete'])
        ->name('roles.destroy');


        // Notification Routes

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    // Route::get('/test-notification', function () {
    //     $user = auth()->user();

    //     $bus = Bus::first();

    //     $user->notify(
    //         new BusStartedNotification($bus)
    //     );

    //     return 'Notification created';
    // })->middleware('auth');

    // Route::get('/test-notification', [BusLocationController::class, 'testNotification'])
    // ->middleware('auth')
    // ->name('test.notification');

    // Route::get('/buttons', function () {
    //     return view('buttons');
    // })->name('buttons');

    // Route::get('/images', function () {
    //     return view('images');
    // })->name('images');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

use App\Models\Bus;
use App\Notifications\BusStartedNotification;
use App\Services\NazarTrackService;

Route::get('/gps-test', function (NazarTrackService $gps) {

    return $gps->findDeviceByImei('868720060002890');

});

require __DIR__.'/auth.php';
