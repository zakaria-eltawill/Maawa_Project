<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_update_name_and_region()
    {
        // Create a user
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Original Region',
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Update name and region
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'name' => 'Updated Name',
                'region' => 'Updated Region',
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('region', 'Updated Region')
            ->assertJsonPath('email', 'user@example.com')
            ->assertJsonPath('phone_number', '+218912345678');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'region' => 'Updated Region',
        ]);
    }

    /** @test */
    public function user_can_update_phone_number()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'phone_number' => '+218912345678',
            'region' => 'Benghazi',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'phone_number' => '+218923456789',
            ]);

        $response->assertOk()
            ->assertJsonPath('phone_number', '+218923456789');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone_number' => '+218923456789',
        ]);
    }

    /** @test */
    public function phone_number_must_be_unique_when_updating()
    {
        // Create two users
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218911111111',
            'region' => 'Tripoli',
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'phone_number' => '+218922222222',
            'region' => 'Benghazi',
        ]);

        // Login as user2
        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user2@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Try to update with user1's phone number
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'phone_number' => '+218911111111', // user1's phone
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Verify user2's phone number was not changed
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'phone_number' => '+218922222222',
        ]);
    }

    /** @test */
    public function user_can_keep_their_own_phone_number()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Update name but keep same phone number
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'name' => 'Updated Name',
                'phone_number' => '+218912345678', // same phone
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('phone_number', '+218912345678');
    }

    /** @test */
    public function user_can_update_password_with_current_password()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('oldpassword'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'oldpassword',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertOk();

        // Verify we can login with new password
        $newLoginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'newpassword123',
        ]);

        $newLoginResponse->assertOk();

        // Verify old password no longer works
        $oldLoginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'oldpassword',
        ]);

        $oldLoginResponse->assertStatus(401);
    }

    /** @test */
    public function password_update_requires_current_password()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Try to update password without current_password
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function password_update_requires_confirmation()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Try to update password without confirmation
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                // missing password_confirmation
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function current_password_must_be_correct()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('correctpassword'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'correctpassword',
        ]);

        $token = $loginResponse->json('access_token');

        // Try to update password with wrong current password
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', 'The current password is incorrect.');
    }

    /** @test */
    public function user_can_update_only_name()
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Tripoli',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'name' => 'Only Name Changed',
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Only Name Changed')
            ->assertJsonPath('phone_number', '+218912345678')
            ->assertJsonPath('region', 'Tripoli');
    }

    /** @test */
    public function user_can_update_only_region()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'phone_number' => '+218912345678',
            'region' => 'Original Region',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'region' => 'New Region',
            ]);

        $response->assertOk()
            ->assertJsonPath('region', 'New Region')
            ->assertJsonPath('name', 'Test User');
    }

    /** @test */
    public function authenticated_user_updates_their_own_profile_only()
    {
        // Create two users
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218911111111',
            'region' => 'Tripoli',
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'phone_number' => '+218922222222',
            'region' => 'Benghazi',
        ]);

        // Login as user1
        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Update profile (authenticated as user1)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'name' => 'User One Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('id', $user1->id)
            ->assertJsonPath('name', 'User One Updated');

        // Verify only user1 was updated
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'name' => 'User One Updated',
        ]);

        // Verify user2 was not affected
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => 'User Two',
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_profile()
    {
        $response = $this->putJson('/v1/me', [
            'name' => 'Should Not Work',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function profile_update_requires_valid_jwt_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->putJson('/v1/me', [
                'name' => 'Should Not Work',
            ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_update_multiple_fields_at_once()
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '+218912345678',
            'region' => 'Original Region',
        ]);

        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/v1/me', [
                'name' => 'New Name',
                'phone_number' => '+218999999999',
                'region' => 'New Region',
            ]);

        $response->assertOk()
            ->assertJsonPath('name', 'New Name')
            ->assertJsonPath('phone_number', '+218999999999')
            ->assertJsonPath('region', 'New Region');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone_number' => '+218999999999',
            'region' => 'New Region',
        ]);
    }
}

