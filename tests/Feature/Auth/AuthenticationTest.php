<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('Driver');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('driver.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_authenticated_driver_visiting_login_is_redirected_to_own_dashboard(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $driver = User::factory()->create();
        $driver->assignRole('Driver');

        $response = $this->actingAs($driver)->get('/login');

        $response->assertRedirect(route('driver.dashboard', absolute: false));
    }

    public function test_authenticated_super_admin_visiting_login_is_redirected_to_super_admin_dashboard(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $response = $this->actingAs($superAdmin)->get('/login');

        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
