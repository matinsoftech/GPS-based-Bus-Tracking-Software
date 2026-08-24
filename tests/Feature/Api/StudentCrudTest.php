<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private User $principal;

    private ParentProfile $parent;

    private Bus $bus;

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

        $this->bus = $this->makeBus($this->school, 'STU-BUS-1');
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

    private function makeBus(School $school, string $busNumber): Bus
    {
        return Bus::create([
            'school_id' => $school->id,
            'bus_number' => $busNumber,
            'registration_number' => 'BA '.$busNumber,
            'capacity' => 40,
            'status' => 'Active',
        ]);
    }

    private function makeStudent(School $school, ParentProfile $parent, ?Bus $bus = null, array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'bus_id' => $bus?->id,
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
            'bus_id' => $this->bus->id,
            'is_active' => true,
        ], $overrides);
    }

    public function test_index_returns_only_own_school_students(): void
    {
        $this->makeStudent($this->school, $this->parent, $this->bus);
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
                            'bus' => ['id', 'bus_number', 'registration_number', 'status'],
                            'school' => ['id', 'name'],
                        ],
                    ],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
            ]);
    }

    public function test_index_search_by_query(): void
    {
        $this->makeStudent($this->school, $this->parent, $this->bus, ['first_name' => 'Gopal', 'last_name' => 'Tamang', 'grade' => '7']);
        $this->makeStudent($this->school, $this->parent, $this->bus, ['first_name' => 'Sita', 'last_name' => 'Sharma', 'grade' => '5']);

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

        $this->postJson('/api/v1/students', $this->validPayload(['admission_no' => 'ADM-NEW-1']))
            ->assertCreated()
            ->assertJsonPath('message', 'Student created successfully.')
            ->assertJsonPath('data.student.full_name', 'Rita Shrestha')
            ->assertJsonPath('data.student.admission_no', 'ADM-NEW-1')
            ->assertJsonPath('data.student.school.id', $this->school->id)
            ->assertJsonPath('data.student.parent.id', $this->parent->id)
            ->assertJsonPath('data.student.bus.id', $this->bus->id);

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

        $existing = $this->makeStudent($this->school, $this->parent, $this->bus);

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

    public function test_create_student_rejects_bus_from_other_school(): void
    {
        $otherBus = $this->makeBus($this->otherSchool, 'OTH-BUS-1');

        Sanctum::actingAs($this->principal);

        $this->postJson('/api/v1/students', $this->validPayload(['bus_id' => $otherBus->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['bus_id']);
    }

    public function test_show_returns_student_details(): void
    {
        $student = $this->makeStudent($this->school, $this->parent, $this->bus);

        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students/'.$student->id)
            ->assertOk()
            ->assertJsonPath('message', 'Student details.')
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.student.full_name', 'Sita Sharma')
            ->assertJsonPath('data.student.bus.bus_number', 'STU-BUS-1')
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

    public function test_show_returns_clean_404_for_nonexistent_student(): void
    {
        Sanctum::actingAs($this->principal);

        $this->getJson('/api/v1/students/999999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_principal_can_update_student(): void
    {
        $student = $this->makeStudent($this->school, $this->parent, $this->bus);

        Sanctum::actingAs($this->principal);

        $this->putJson('/api/v1/students/'.$student->id, $this->validPayload([
            'admission_no' => $student->admission_no,
            'first_name' => 'Rita',
            'grade' => '6',
        ]))
            ->assertOk()
            ->assertJsonPath('message', 'Student updated successfully.')
            ->assertJsonPath('data.student.first_name', 'Rita')
            ->assertJsonPath('data.student.grade', '6');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Rita',
            'grade' => '6',
        ]);
    }

    public function test_update_rejects_duplicate_admission_no(): void
    {
        $student = $this->makeStudent($this->school, $this->parent, $this->bus);
        $other = $this->makeStudent($this->school, $this->parent, $this->bus);

        Sanctum::actingAs($this->principal);

        $this->putJson('/api/v1/students/'.$student->id, $this->validPayload(['admission_no' => $other->admission_no]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['admission_no']);
    }

    public function test_update_rejects_student_from_other_school(): void
    {
        $otherParent = $this->makeParent($this->otherSchool, 'Other Parent', '9800000605');
        $student = $this->makeStudent($this->otherSchool, $otherParent);

        Sanctum::actingAs($this->principal);

        $this->putJson('/api/v1/students/'.$student->id, $this->validPayload())
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this student.');
    }

    public function test_principal_can_delete_student(): void
    {
        $student = $this->makeStudent($this->school, $this->parent, $this->bus);

        Sanctum::actingAs($this->principal);

        $this->deleteJson('/api/v1/students/'.$student->id)
            ->assertOk()
            ->assertJsonPath('message', 'Student deleted successfully.');

        $this->assertSoftDeleted('students', ['id' => $student->id]);

        $this->getJson('/api/v1/students/'.$student->id)
            ->assertNotFound()
            ->assertJsonPath('message', 'Student not found.');
    }

    public function test_delete_rejects_student_from_other_school(): void
    {
        $otherParent = $this->makeParent($this->otherSchool, 'Other Parent', '9800000606');
        $student = $this->makeStudent($this->otherSchool, $otherParent);

        Sanctum::actingAs($this->principal);

        $this->deleteJson('/api/v1/students/'.$student->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this student.');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }

    public function test_non_school_admin_role_is_forbidden(): void
    {
        $parent = User::factory()->create();
        $parent->assignRole('Parent');

        $student = $this->makeStudent($this->school, $this->parent, $this->bus);

        Sanctum::actingAs($parent);

        $this->getJson('/api/v1/students')->assertForbidden();
        $this->postJson('/api/v1/students', $this->validPayload())->assertForbidden();
        $this->getJson('/api/v1/students/'.$student->id)->assertForbidden();
        $this->putJson('/api/v1/students/'.$student->id, $this->validPayload())->assertForbidden();
        $this->deleteJson('/api/v1/students/'.$student->id)->assertForbidden();
    }

    public function test_school_admin_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create([
            'school_id' => $this->school->id,
        ]);
        $user->assignRole('School Admin');
        Role::findByName('School Admin')->revokePermissionTo('student.create');

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/students', $this->validPayload())
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this resource.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_dashboard_without_school_resolution_returns_404(): void
    {
        $user = User::factory()->create([
            'name' => 'Floating Admin',
            'email' => 'floating.stu@example.com',
            'school_id' => null,
        ]);
        $user->assignRole('School Admin');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/students')
            ->assertNotFound()
            ->assertJsonPath('message', 'Principal profile not found.');
    }
}
