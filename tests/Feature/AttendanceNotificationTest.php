<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAttendanceNotification;
use App\Services\AttendanceNotificationService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceNotificationTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Driver $driver;

    private User $driverUser;

    private Route $route;

    private ParentProfile $parent;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->driverUser = User::factory()->create(['email' => 'attend-notif-driver@example.com']);
        $this->driverUser->assignRole('Driver');

        $parentUser = User::factory()->create(['email' => 'attend-notif-parent@example.com']);
        $parentUser->assignRole('Parent');

        $this->school = School::create([
            'name' => 'Attendance Notif School',
            'code' => 'SCH-AN-1',
            'email' => 'an@school.com',
            'phone' => '9800000400',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->parent = ParentProfile::create([
            'school_id' => $this->school->id,
            'user_id' => $parentUser->id,
            'name' => 'Parent Notif',
            'father_name' => 'Papa',
            'phone' => '9800000401',
            'address' => 'Kathmandu',
        ]);

        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-AN-1',
            'first_name' => 'Notif',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000402',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-AN-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);

        $this->route = Route::create([
            'name' => 'Baneshwor Shuttle',
            'route_code' => 'RT-AN-1',
            'school_id' => $this->school->id,
            'is_active' => true,
            'start_location' => 'Baneshwor',
            'end_location' => 'School',
        ]);

        $this->driver->routes()->attach($this->route->id);

        $this->student = $this->createStudent('ADM-AN-1', $this->parent, $this->route);
    }

    private function createStudent(string $admissionNo, ParentProfile $parent, Route $route): Student
    {
        return Student::create([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'route_id' => $route->id,
            'admission_no' => $admissionNo,
            'first_name' => 'Kid',
            'last_name' => 'One',
            'gender' => 'Male',
            'grade' => '5',
            'pickup_location' => 'Baneshwor',
            'drop_location' => 'School',
            'date_of_birth' => '2015-01-01',
            'is_active' => true,
            'created_by' => $this->driverUser->id,
        ]);
    }

    private function markViaApi(): TestResponse
    {
        Sanctum::actingAs($this->driverUser, ['*']);

        return $this->postJson('/api/v1/driver/attendances/mark', [
            'route_id' => $this->route->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_first_mark_notifies_parent_of_pickup_and_returns_data(): void
    {
        $response = $this->markViaApi();

        $response->assertOk()
            ->assertJsonPath('data.action', 'picked_up_home')
            ->assertJsonPath('data.action_label', 'Pick Up')
            ->assertJsonPath('data.route_id', $this->route->id)
            ->assertJsonPath('data.route_name', 'Baneshwor Shuttle')
            ->assertJsonPath('data.student_full_name', 'Kid One');

        $notification = $this->parent->user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame(StudentAttendanceNotification::class, $notification->type);

        $payload = $notification->data;
        $this->assertSame('attendance', $payload['type']);
        $this->assertSame('picked_up_home', $payload['action']);
        $this->assertSame('Kid One', $payload['student_name']);
    }

    public function test_only_marked_student_parent_is_notified(): void
    {
        $otherParent = ParentProfile::create([
            'school_id' => $this->school->id,
            'user_id' => User::factory()->create(['email' => 'other-parent@example.com'])->id,
            'name' => 'Other',
            'father_name' => 'Other Papa',
            'phone' => '9800000403',
            'address' => 'Kathmandu',
        ]);

        $this->createStudent('ADM-AN-2', $otherParent, $this->route);

        $this->markViaApi()->assertOk();

        $this->assertDatabaseCount('notifications', 1);

        $markedParentNotification = $this->parent->user->notifications()->first();
        $this->assertNotNull($markedParentNotification);
        $this->assertSame('Kid One', $markedParentNotification->data['student_name']);
        $this->assertSame('picked_up_home', $markedParentNotification->data['action']);

        $this->assertNull($otherParent->user->notifications()->first());
    }

    public function test_all_four_stages_notify_parent_in_sequence(): void
    {
        $expectedKeys = ['picked_up_home', 'dropped_at_school', 'picked_up_school', 'dropped_at_home'];

        foreach ($expectedKeys as $key) {
            $this->markViaApi()->assertOk();
        }

        $notifications = $this->parent->user->notifications()->get();
        $this->assertCount(4, $notifications);

        $actions = $notifications->map(fn ($n) => $n->data['action'])->all();
        $this->assertSame($expectedKeys, $actions);
    }

    public function test_service_notifies_only_when_parent_present(): void
    {
        $this->parent->user->notifications()->delete();

        app(AttendanceNotificationService::class)->notifyParent(
            $this->student,
            'picked_up_home',
            now()->toIso8601String(),
        );

        $this->assertCount(1, $this->parent->user->notifications()->get());
    }
}
