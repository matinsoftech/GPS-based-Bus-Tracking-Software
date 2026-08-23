<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_user_can_upload_profile_photo_from_profile_page(): void
    {
        Storage::fake('public');

        $user = $this->superAdmin();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->profile_photo);
        $this->assertStringStartsWith('users/', $user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_replacing_the_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');

        $user = $this->superAdmin();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $oldPath = $user->refresh()->profile_photo;

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $newPath = $user->refresh()->profile_photo;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_super_admin_can_set_photo_via_user_management_and_delete_cleans_up(): void
    {
        Storage::fake('public');

        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Photo User',
            'email' => 'photo@example.com',
            'password' => 'password',
            'role' => 'Super Admin',
            'status' => 'active',
            'profile_photo' => UploadedFile::fake()->image('created.jpg'),
        ])->assertRedirect(route('users.index'));

        $created = User::where('email', 'photo@example.com')->firstOrFail();
        $this->assertNotNull($created->profile_photo);
        Storage::disk('public')->assertExists($created->profile_photo);

        $this->actingAs($admin)->put(route('users.update', $created), [
            'name' => $created->name,
            'email' => $created->email,
            'role' => 'Super Admin',
            'status' => 'active',
            'profile_photo' => UploadedFile::fake()->image('replaced.jpg'),
        ])->assertRedirect(route('users.index'));

        $created->refresh();
        Storage::disk('public')->assertExists($created->profile_photo);

        $path = $created->profile_photo;

        $this->actingAs($admin)->delete(route('users.destroy', $created))
            ->assertRedirect(route('users.index'));

        Storage::disk('public')->assertMissing($path);
    }
}
