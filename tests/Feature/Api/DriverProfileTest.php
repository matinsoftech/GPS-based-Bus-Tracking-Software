<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\School;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;

    private Driver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $school = School::create([
            'name' => 'Bright Future School',
            'code' => 'SCH-DRIVER-1',
            'email' => 'driver@brightfuture.com',
            'phone' => '9800000100',
            'address' => 'Kathmandu',
            'status' => 'active',
        ]);

        $this->driverUser = User::factory()->create([
            'name' => 'Ramesh Sharma',
            'email' => 'ramesh@example.com',
        ]);
        $this->driverUser->assignRole('Driver');

        $this->driver = Driver::create([
            'school_id' => $school->id,
            'user_id' => $this->driverUser->id,
            'employee_id' => 'DR-PROFILE-1',
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'phone' => '9800000101',
            'address' => 'Kathmandu',
            'license_number' => 'LIC-PROFILE-1',
            'license_type' => 'Bus',
            'license_issue_date' => '2020-01-01',
            'license_expiry_date' => '2030-01-01',
            'joining_date' => '2024-01-01',
            'status' => 'Active',
            'created_by' => $this->driverUser->id,
        ]);
    }

    public function test_driver_can_view_their_profile(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->getJson('/api/v1/driver/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ramesh Sharma')
            ->assertJsonPath('data.phone', '9800000101')
            ->assertJsonPath('data.photo_url', null)
            ->assertJsonPath('data.role', 'Driver')
            ->assertJsonPath('data.school.name', 'Bright Future School')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'photo_url',
                    'phone',
                    'license_number',
                    'address',
                    'role',
                    'status',
                    'school' => ['id', 'name', 'address'],
                ],
            ]);
    }

    public function test_driver_can_update_mini_profile(): void
    {
        Sanctum::actingAs($this->driverUser);

        $response = $this->putJson('/api/v1/driver/profile', [
            'name' => 'Ramesh Prasad Sharma',
            'phone' => '9811111111',
            'address' => 'Lalitpur',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Driver profile updated.')
            ->assertJsonPath('data.name', 'Ramesh Prasad Sharma')
            ->assertJsonPath('data.phone', '9811111111');

        $this->assertDatabaseHas('users', [
            'id' => $this->driverUser->id,
            'name' => 'Ramesh Prasad Sharma',
        ]);

        $this->driver->refresh();
        $this->assertSame('Ramesh', $this->driver->first_name);
        $this->assertSame('Prasad Sharma', $this->driver->last_name);
        $this->assertSame('9811111111', $this->driver->phone);
        $this->assertSame('Lalitpur', $this->driver->address);
    }

    public function test_driver_profile_update_validates_required_fields(): void
    {
        Sanctum::actingAs($this->driverUser);

        $this->putJson('/api/v1/driver/profile', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'address']);
    }

    public function test_non_driver_user_gets_404_on_update(): void
    {
        $plainUser = User::factory()->create();

        Sanctum::actingAs($plainUser);

        $this->putJson('/api/v1/driver/profile', [
            'name' => 'X',
            'phone' => '9800000000',
            'address' => 'Kathmandu',
        ])->assertNotFound();

        $this->postJson('/api/v1/driver/profile/photo', [
            'profile_photo' => UploadedFile::fake()->image('me.jpg'),
        ])->assertNotFound();
    }

    public function test_driver_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        Sanctum::actingAs($this->driverUser);

        $response = $this->postJson('/api/v1/driver/profile/photo', [
            'profile_photo' => UploadedFile::fake()->image('me.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Profile photo updated.');

        $path = $this->driver->refresh()->profile_photo;

        $this->assertNotNull($path);
        $this->assertStringStartsWith('drivers/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_driver_photo_deletes_old_file(): void
    {
        Storage::fake('public');

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/profile/photo', [
            'profile_photo' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $oldPath = $this->driver->refresh()->profile_photo;

        $this->postJson('/api/v1/driver/profile/photo', [
            'profile_photo' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $newPath = $this->driver->refresh()->profile_photo;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_driver_photo_upload_rejects_invalid_file(): void
    {
        Storage::fake('public');

        Sanctum::actingAs($this->driverUser);

        $this->postJson('/api/v1/driver/profile/photo', [
            'profile_photo' => UploadedFile::fake()->create('notes.txt', 100),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['profile_photo']);

        $this->postJson('/api/v1/driver/profile/photo', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['profile_photo']);
    }
}
