<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Driver;
use App\Models\ParentProfile;
use App\Models\School;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ApiPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_request_password_reset_link(): void
    {
        Notification::fake();

        $school = School::create([
            'name' => 'Test School',
            'code' => 'TS001',
            'email' => 'school@test.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['email' => 'driver@test.com']);
        Driver::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'employee_id' => 'EMP001',
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'gender' => 'Male',
            'phone' => '1234567890',
            'email' => 'driver@test.com',
            'address' => 'Test Address',
            'license_number' => 'LIC123',
            'license_type' => 'Heavy',
            'license_issue_date' => now(),
            'license_expiry_date' => now()->addYears(5),
            'experience_years' => 5,
            'joining_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'driver@test.com',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'We have emailed your password reset link.']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_parent_can_request_password_reset_link(): void
    {
        Notification::fake();

        $school = School::create([
            'name' => 'Test School',
            'code' => 'TS001',
            'email' => 'school@test.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['email' => 'parent@test.com']);
        ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'name' => 'Test Parent',
            'phone' => '1234567890',
            'address' => 'Test Address',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'parent@test.com',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'We have emailed your password reset link.']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_error_for_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertBadRequest();
    }

    public function test_forgot_password_validates_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@test.com']);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@test.com',
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->postJson('/api/v1/auth/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertOk()
                ->assertJson(['message' => 'Your password has been reset.']);

            $user->refresh();
            $this->assertTrue(Hash::check('newpassword123', $user->password));

            return true;
        });
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertBadRequest();
    }

    public function test_reset_password_validates_password(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'some-token',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password changed successfully.']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/change-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_change_password_validates_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_change_password_requires_authentication(): void
    {
        $response = $this->putJson('/api/v1/auth/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertUnauthorized();
    }
}