<?php

namespace Tests\Feature\Api;

use App\Models\ParentProfile;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private User $principal;

    private ParentProfile $parent;

    private Route $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->school = School::create([
            'name' => 'Sunrise Valley School',
            'code' => 'SCH-STU-1',
            'email' => 'sunrise@example.com',
            'phone' => '9800000600',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->otherSchool = School::create([
            'name' => 'Other School',
            'code' => 'SCH-STU-2',
            'email' => 'other@example.com',
            'phone' => '9800000601',
            'address' => 'Lalitpur',
            'status' => 'active',
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Principal Alpha',
            'email' => 'principal@sunrise.example.com',
            'school_id' => $this->school->id,
        ]);
        $this->principal->assignRole('School Admin');

        SchoolAdmin::create([
            'user_id' => $this->principal->id,
            'school_id' => $this->school->id,
            'name' => 'Principal Alpha',
            'phone' => '9812345678',
            'designation' => 'Principal',
            'address' => 'Kathmandu',
        ]);

        $this->parent = $this->makeParent($this->school, 'Hari Sharma', '9812345678');

        $this->route = $this->makeRoute($this->school, 'STU-ROUTE-1');
    }

    private function makeParent(School $school, string $name, string $phone): ParentProfile
    {
        $user = User::factory()->create(['name' => $name]);
        $user->assignRole('Parent');

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => $name,
            'phone' => $phone,
            'address' => 'Kathmandu',
        ]);
    }

    private function makeRoute(School $school, string $name): Route
    {
        return Route::create([
            'name' => $name,
            'route_code' => 'RT-'.strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8)).'-'.bin2hex(random_bytes(3)),
            'school_id' => $school->id,
            'start_location' => 'Start',
            'end_location' => 'End',
            'is_active' => true,
        ]);
    }

    private function makeStudent(School $school, ParentProfile $parent, ?Route $route = null, array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'route_id' => $route?->id,
            'admission_no' => 'ADM-STU-'.uniqid(),
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'admission_no' => 'ADM-NEW-'.uniqid(),
            'first_name' => 'Rita',
            'last_name' => 'Shrestha',
            'date_of_birth' => '2013-05-10',
            'gender' => 'Female',
            'grade' => '4',
            'section' => 'B',
            'roll_no' => '2',
            'pickup_location' => 'Baneshwor',
            'drop_location' => 'School',
            'parent_id' => $this->parent->id,
            'route_id' => $this->route->id,
            'is_active' => true,
        ], $overrides);
    }

    public function test_index_returns_only_own_school_students(): void
    {
        $this->makeStudent($this->school, $this->parent, $this->route);
        $otherParent = $this->makeParent($this->otherSchool, 'Other Parent', '9800000602');
        $this->makeStudent($this->otherSchool, $otherParent);

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students')
            ->assertOk()
            ->assertJsonPath('message', 'Students list.')
            ->assertJsonCount(1, 'data.students')
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'students' => [
                        '*' => [
                            'id',
                            'admission_no',
                            'full_name',
                            'grade',
                            'section',
                            'is_active',
                            'parent' => ['id', 'name', 'phone'],
                            'route' => ['id', 'name', 'is_active'],
                            'school' => ['id', 'name'],
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
            ]);
    }

    public function test_index_search_by_query(): void
    {
        $this->makeStudent($this->school, $this->parent, $this->route, ['first_name' => 'Gopal', 'last_name' => 'Tamang', 'grade' => '7']);
        $this->makeStudent($this->school, $this->parent, $this->route, ['first_name' => 'Sita', 'last_name' => 'Sharma', 'grade' => '5']);

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students?q=Gopal')
            ->assertOk()
            ->assertJsonCount(1, 'data.students')
            ->assertJsonPath('data.students.0.first_name', 'Gopal');

        $this->getJson('/api/v1/students?q=Hari')
            ->assertOk()
            ->assertJsonCount(2, 'data.students');
    }

    public function test_principal_can_create_student(): void
    {
        Sanctum::actingAs($this->principal);

        $this->postJson('/api/v1/students', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('message', 'Student created successfully.')
            ->assertJsonPath('data.student.full_name', 'Rita Shrestha')
            ->assertJsonPath('data.student.admission_no', 'ADM-NEW-1')
            ->assertJsonPath('data.student.school.id', $this->school->id)
            ->assertJsonPath('data.student.parent.id', $this->parent->id)
            ->assertJsonPath('data.student.route.id', $this->route->id);

        $this->assertDatabaseHas('students', [
            'admission_no' => 'ADM-NEW-1',
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
        ]);
    }

    public function test_create_student_validates_fields(): void
    {
        Sanctum::actingAs($this->principal);

        $this->postJson('/api/v1/students', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['admission_no', 'first_name', 'date_of_birth', 'gender', 'grade', 'pickup_location', 'drop_location', 'parent_id']);

        $this->postJson('/api/v1/students', $this->validPayload([
            'date_of_birth' => now()->addDay()->toDateString(),
            'gender' => 'Unknown',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_of_birth', 'gender']);

        $existing = $this->makeStudent($this->school, $this->parent, $this->route);

        $this->postJson('/api/v1/students', $this->validPayload(['admission_no' => $existing->admission_no]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['admission_no']);
    }

    public function test_create_student_rejects_parent_from_other_school(): void
    {
        $otherParent = $this->makeParent($this->otherSchool, 'Other Parent', '9800000603');

        Sanctum::actingAs($this->principal);

        $this->postJson('/api/v1/students', $this->validPayload(['parent_id' => $otherParent->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_create_student_rejects_route_from_other_school(): void
    {
        $otherRoute = $this->makeRoute($this->otherSchool, 'OTH-ROUTE-1');

        Sanctum::actingAs($this->principal);

        $this->postJson('/api/v1/students', $this->validPayload(['route_id' => $otherRoute->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['route_id']);
    }

    public function test_show_returns_student_details(): void
    {
        $student = $this->makeStudent($this->school, $this->parent, $this->route);

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students/'.$student->id)
            ->assertOk()
            ->assertJsonPath('message', 'Student details.')
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.student.full_name', 'Sita Sharma')
            ->assertJsonPath('data.student.route.name', 'STU-ROUTE-1')
            ->assertJsonPath('data.student.school.id', $this->school->id);
    }

    public function test_show_rejects_student_from_other_school(): void
    {
        $otherParent = $this->makeParent($this->otherSchool, 'Other Parent', '9800000604');
        $student = $this->makeStudent($this->otherSchool, $otherParent);

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students/'.$student->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this student.');
    }
}
