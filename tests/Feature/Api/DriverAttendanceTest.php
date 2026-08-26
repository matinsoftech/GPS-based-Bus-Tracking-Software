<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Driver $driver;

    private User $driverUser;

    private Bus $bus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-API-1',
            'email' => 'api@brightfuture.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create();
        $this->driver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-API-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000001',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-API-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);

        $this->bus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'API-BUS-1',
            'registration_number' => 'BA API-BUS-1',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $this->bus->drivers()->attach($this->driver->id);
    }

    private function makeStudent(array $overrides = []): Student
    {
        $parent = ParentProfile::create([
            'user_id' => $this->driverUser->id,
            'school_id' => $this->school->id,
            'name' => 'Hari Bahadur',
            'phone' => '9812345678',
            'address' => 'Kathmandu',
        ]);

        return Student::create(array_merge([
            'school_id' => $this->school->id,
            'parent_id' => $parent->id,
            'bus_id' => $this->bus->id,
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => 'Sita',
            'last_name' => 'Sharma',
            'date_of_birth' => '2012-05-10',
            'gender' => 'Female',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Chabahil',
            'drop_location' => 'School',
            'is_active' => true,
        ], $overrides));
    }

    public function test_driver_can_list_students_of_assigned_bus(): void
    {
        $this->makeStudent();
        $this->makeStudent(['first_name' => 'Rita', 'roll_no' => '2']);

        Sanctum::actingAs($this->driverUser);

        $response = $this->getJson('/api/v1/driver/attendances?bus_id='.$this->bus->id);

        $response->assertOk()
            ->assertJsonPath('data.total_students', 2)
            ->assertJsonCount(2, 'data.students')
            ->assertJsonPath('data.students.0.full_name', 'Sita Sharma')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'bus' => ['id', 'bus_number', 'registration_number', 'status'],
                    'total_students',
                    'students' => [
                        '*' => [
                            'id',
                            'admission_no',
                            'first_name',
                            'last_name',
                            'full_name',
                            'gender',
                            'grade',
                            'section',
                            'roll_no',
                            'photo',
                            'pickup_location',
                            'drop_location',
                            'parent',
                        ],
                    ],
                ],
            ]);
    }

    public function test_bus_id_is_required(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bus_id');
    }

    public function test_cannot_view_another_drivers_bus(): void
    {
        $otherDriverUser = User::factory()->create();
        $otherDriver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $otherDriverUser->id,
            'employee_id' => 'DR-API-2',
            'first_name' => 'Other',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1985-01-01',
            'phone' => '9800000002',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-API-2',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $otherDriverUser->id,
        ]);

        $otherBus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'API-BUS-2',
            'registration_number' => 'BA API-BUS-2',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $otherBus->drivers()->attach($otherDriver->id);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances?bus_id='.$otherBus->id)
            ->assertNotFound();
    }

    public function test_user_without_driver_profile_gets_404(): void
    {
        $parentUser = User::factory()->create();

        Sanctum::actingAs($parentUser);

        $this->getJson('/api/v1/driver/attendances?bus_id='.$this->bus->id)
            ->assertNotFound();
    }

    public function test_driver_can_mark_full_attendance_sequence(): void
    {
        $student = $this->makeStudent();

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Sita Sharma picked up from home.')
            ->assertJsonPath('data.trip', 'home_to_school')
            ->assertJsonStructure(['data' => ['check_in_at']]);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Sita Sharma dropped at school.')
            ->assertJsonStructure(['data' => ['check_out_at']]);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Sita Sharma picked up from school.')
            ->assertJsonPath('data.trip', 'school_to_home');

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Sita Sharma dropped at home.')
            ->assertJsonPath('data.trip', 'school_to_home');

        $this->assertDatabaseCount('attendances', 2);

        $home = Attendance::where('student_id', $student->id)
            ->where('trip', 'home_to_school')
            ->whereDate('date', now())
            ->first();
        $this->assertNotNull($home->check_in_at);
        $this->assertNotNull($home->check_out_at);

        $school = Attendance::where('student_id', $student->id)
            ->where('trip', 'school_to_home')
            ->whereDate('date', now())
            ->first();
        $this->assertNotNull($school->check_in_at);
        $this->assertNotNull($school->check_out_at);
    }

    public function test_fifth_mark_is_rejected_when_day_completed(): void
    {
        $student = $this->makeStudent();

        Sanctum::actingAs($this->driverUser);

        foreach (range(1, 4) as $i) {
            $this->postJson('/api/v1/driver/attendances/mark', [
                'bus_id' => $this->bus->id,
                'student_id' => $student->id,
            ])->assertOk();
        }

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Sita Sharma\'s attendance is already completed for today.');
    }

    public function test_school_to_home_is_not_reached_until_home_to_school_is_completed(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now(),
            'check_in_at' => now()->subHours(2),
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Sita Sharma dropped at school.')
            ->assertJsonPath('data.trip', 'home_to_school')
            ->assertJsonStructure(['data' => ['check_out_at']]);
    }

    public function test_cannot_mark_student_not_assigned_to_bus(): void
    {
        $otherDriverUser = User::factory()->create();
        $otherDriver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $otherDriverUser->id,
            'employee_id' => 'DR-API-3',
            'first_name' => 'Third',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1988-01-01',
            'phone' => '9800000003',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-API-3',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $otherDriverUser->id,
        ]);

        $otherBus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'API-BUS-3',
            'registration_number' => 'BA API-BUS-3',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $otherBus->drivers()->attach($otherDriver->id);

        $student = $this->makeStudent(['bus_id' => $otherBus->id]);

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Student not found on this bus.');
    }

    public function test_cannot_mark_on_inactive_bus(): void
    {
        $this->bus->update(['status' => 'Inactive']);

        $student = $this->makeStudent();

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $this->bus->id,
            'student_id' => $student->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Attendance can only be marked on active buses.');
    }

    public function test_mark_requires_bus_id_and_student_id(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['bus_id', 'student_id']);
    }

    public function test_cannot_mark_on_another_drivers_bus(): void
    {
        $otherDriverUser = User::factory()->create();
        $otherDriver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $otherDriverUser->id,
            'employee_id' => 'DR-API-4',
            'first_name' => 'Fourth',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1987-01-01',
            'phone' => '9800000004',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-API-4',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $otherDriverUser->id,
        ]);

        $otherBus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'API-BUS-4',
            'registration_number' => 'BA API-BUS-4',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $otherBus->drivers()->attach($otherDriver->id);

        $student = $this->makeStudent();

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/attendances/mark', [
            'bus_id' => $otherBus->id,
            'student_id' => $student->id,
        ])
            ->assertNotFound();
    }

    public function test_driver_can_view_attendance_history_for_assigned_bus(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now(),
            'check_in_at' => now()->setTime(7, 15, 0),
            'check_out_at' => now()->setTime(8, 0, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'school_to_home',
            'date' => now(),
            'check_in_at' => now()->setTime(15, 30, 0),
            'check_out_at' => now()->setTime(16, 10, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.total_records', 2)
            ->assertJsonCount(2, 'data.records')
            ->assertJsonPath('data.records.0.trip_label', 'School to Home (Drop)')
            ->assertJsonPath('data.records.0.status', 'completed')
            ->assertJsonPath('data.records.0.student.full_name', 'Sita Sharma')
            ->assertJsonPath('data.records.0.marked_by.name', $this->driverUser->name)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'bus' => ['id', 'bus_number', 'registration_number', 'status'],
                    'from',
                    'to',
                    'total_records',
                    'records' => [
                        '*' => [
                            'id',
                            'date',
                            'trip',
                            'trip_label',
                            'check_in_at',
                            'check_out_at',
                            'status',
                            'student',
                            'marked_by',
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'last_page', 'total', 'from', 'to'],
                ],
            ]);
    }

    public function test_history_requires_bus_id(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bus_id');
    }

    public function test_cannot_view_history_for_another_drivers_bus(): void
    {
        $otherDriverUser = User::factory()->create();
        $otherDriver = Driver::create([
            'school_id' => $this->school->id,
            'user_id' => $otherDriverUser->id,
            'employee_id' => 'DR-API-5',
            'first_name' => 'Fifth',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'date_of_birth' => '1986-01-01',
            'phone' => '9800000005',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-API-5',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $otherDriverUser->id,
        ]);

        $otherBus = Bus::create([
            'school_id' => $this->school->id,
            'bus_number' => 'API-BUS-5',
            'registration_number' => 'BA API-BUS-5',
            'capacity' => 40,
            'status' => 'Active',
        ]);
        $otherBus->drivers()->attach($otherDriver->id);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$otherBus->id)
            ->assertNotFound();
    }

    public function test_history_respects_from_and_to_date_filters(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now()->subDays(45),
            'check_in_at' => now()->subDays(45)->setTime(7, 15, 0),
            'check_out_at' => now()->subDays(45)->setTime(8, 0, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now()->subDays(3),
            'check_in_at' => now()->subDays(3)->setTime(7, 15, 0),
            'check_out_at' => now()->subDays(3)->setTime(8, 0, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.total_records', 1);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id
            .'&from='.now()->subDays(50)->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertJsonPath('data.total_records', 2);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id
            .'&from='.now()->subDays(10)->toDateString().'&to='.now()->subDays(5)->toDateString())
            ->assertOk()
            ->assertJsonPath('data.total_records', 0);
    }

    public function test_history_rejects_invalid_date_range(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id
            .'&from='.now()->toDateString().'&to='.now()->subDays(5)->toDateString())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }

    public function test_history_works_for_inactive_bus(): void
    {
        $this->bus->update(['status' => 'Inactive']);

        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now(),
            'check_in_at' => now()->setTime(7, 15, 0),
            'check_out_at' => now()->setTime(8, 0, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances/history?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.total_records', 1);
    }

    public function test_index_includes_today_attendance_status_for_students(): void
    {
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'home_to_school',
            'date' => now(),
            'check_in_at' => now()->setTime(7, 15, 0),
            'check_out_at' => now()->setTime(8, 0, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'bus_id' => $this->bus->id,
            'trip' => 'school_to_home',
            'date' => now(),
            'check_in_at' => now()->setTime(15, 30, 0),
            'marked_by' => $this->driverUser->id,
        ]);

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.students.0.today_attendance.home_to_school.status', 'completed')
            ->assertJsonPath('data.students.0.today_attendance.school_to_home.status', 'checked_in')
            ->assertJsonPath('data.students.0.today_attendance.completed', false)
            ->assertJsonPath('data.students.0.today_attendance.next_action.key', 'dropped_at_home')
            ->assertJsonStructure([
                'data' => [
                    'students' => [
                        '*' => [
                            'today_attendance' => [
                                'home_to_school' => ['check_in_at', 'check_out_at', 'status'],
                                'school_to_home' => ['check_in_at', 'check_out_at', 'status'],
                                'completed',
                                'next_action',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_index_shows_not_started_when_student_has_no_records(): void
    {
        $this->makeStudent();

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.students.0.today_attendance.home_to_school.status', 'not_checked_in')
            ->assertJsonPath('data.students.0.today_attendance.school_to_home.status', 'not_checked_in')
            ->assertJsonPath('data.students.0.today_attendance.completed', false)
            ->assertJsonPath('data.students.0.today_attendance.next_action.key', 'picked_up_home');
    }

    public function test_index_shows_completed_when_all_four_stages_done(): void
    {
        $student = $this->makeStudent();

        foreach (['home_to_school', 'school_to_home'] as $trip) {
            Attendance::create([
                'student_id' => $student->id,
                'bus_id' => $this->bus->id,
                'trip' => $trip,
                'date' => now(),
                'check_in_at' => now()->setTime(7, 15, 0),
                'check_out_at' => now()->setTime(16, 0, 0),
                'marked_by' => $this->driverUser->id,
            ]);
        }

        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/attendances?bus_id='.$this->bus->id)
            ->assertOk()
            ->assertJsonPath('data.students.0.today_attendance.home_to_school.status', 'completed')
            ->assertJsonPath('data.students.0.today_attendance.school_to_home.status', 'completed')
            ->assertJsonPath('data.students.0.today_attendance.completed', true)
            ->assertJsonPath('data.students.0.today_attendance.next_action', null);
    }
}
