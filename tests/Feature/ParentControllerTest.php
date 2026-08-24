<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_only_sees_parents_from_their_own_school(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolB = $this->createSchool('Moonlight School', 'SCH-B');

        $admin = User::factory()->create([
            'school_id' => $schoolA->id,
        ]);
        $admin->assignRole('School Admin');

        $ownParent = $this->createParent('Own Parent', 'own@example.com', $schoolA);
        $otherParent = $this->createParent('Other Parent', 'other@example.com', $schoolB);

        $response = $this->actingAs($admin)->get(route('parents.index'));
        $response->assertOk();
        $response->assertSee('Own Parent');
        $response->assertDontSee('Other Parent');

        $response = $this->actingAs($admin)->get(route('parents.show', $otherParent));
        $response->assertForbidden();

        $response = $this->actingAs($admin)->get(route('parents.show', $ownParent));
        $response->assertOk();
    }

    public function test_school_admin_cannot_create_or_move_parent_to_another_school(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $schoolA = $this->createSchool('Sunrise Academy', 'SCH-A');
        $schoolB = $this->createSchool('Moonlight School', 'SCH-B');

        $admin = User::factory()->create([
            'school_id' => $schoolA->id,
        ]);
        $admin->assignRole('School Admin');

        $response = $this->actingAs($admin)->post(route('parents.store'), [
            'name' => 'New Parent',
            'email' => 'new@example.com',
            'password' => 'password123',
            'school_id' => $schoolB->id,
            'phone' => '9800000000',
            'alternate_phone' => null,
            'address' => 'Kathmandu',
            'occupation' => 'Engineer',
        ]);

        $response->assertRedirect(route('parents.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'school_id' => $schoolA->id,
        ]);

        $parent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', 'new@example.com'))->first();
        $this->assertNotNull($parent);
        $this->assertSame($schoolA->id, $parent->school_id);

        $response = $this->actingAs($admin)->put(route('parents.update', $parent), [
            'name' => 'New Parent',
            'email' => 'new@example.com',
            'school_id' => $schoolB->id,
            'phone' => '9800000000',
            'address' => 'Kathmandu',
        ]);

        $response->assertRedirect(route('parents.index'));

        $parent->refresh();
        $this->assertSame($schoolA->id, $parent->school_id);
        $this->assertSame($schoolA->id, $parent->user->school_id);
    }

    public function test_parent_can_view_their_own_child_attendance_only(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');

        $parentA = $this->createParent('Parent A', 'parenta@example.com', $school);
        $parentB = $this->createParent('Parent B', 'parentb@example.com', $school);

        $ownChild = $this->createStudent($parentA, 'Alice Child');
        $otherChild = $this->createStudent($parentB, 'Bob Other');

        Attendance::create([
            'student_id' => $ownChild->id,
            'trip' => Attendance::TRIP_HOME_TO_SCHOOL,
            'date' => now()->toDateString(),
            'check_in_at' => now()->subHours(3),
            'check_out_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($parentA->user)->get(route('parent.student.attendance', $ownChild));

        $response->assertOk();
        $response->assertSee('Alice Child');
        $response->assertSee('Home to School (Pickup)');
        $response->assertDontSee('Bob Other');
    }

    public function test_parent_cannot_view_another_parents_child_attendance(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = $this->createSchool('Sunrise Academy', 'SCH-A');

        $parentA = $this->createParent('Parent A', 'parenta@example.com', $school);
        $parentB = $this->createParent('Parent B', 'parentb@example.com', $school);

        $otherChild = $this->createStudent($parentB, 'Bob Other');

        $response = $this->actingAs($parentA->user)->get(route('parent.student.attendance', $otherChild));

        $response->assertForbidden();
    }

    private function createSchool(string $name, string $code): School
    {
        return School::create([
            'name' => $name,
            'code' => $code,
            'email' => strtolower($code).'@school.com',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
            'principal_name' => 'Principal',
            'status' => 'active',
        ]);
    }

    private function createParent(string $name, string $email, School $school): ParentProfile
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'school_id' => $school->id,
        ]);
        $user->assignRole('Parent');

        return ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => $name,
            'phone' => '9800000000',
            'address' => 'Kathmandu',
        ]);
    }

    private function createStudent(ParentProfile $parent, string $name): Student
    {
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');

        return Student::create([
            'school_id' => $parent->school_id,
            'parent_id' => $parent->id,
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => '2015-05-10',
            'gender' => 'Male',
            'grade' => '5',
            'section' => 'A',
            'roll_no' => '1',
            'pickup_location' => 'Kathmandu',
            'drop_location' => 'Kathmandu',
            'is_active' => true,
        ]);
    }
}
