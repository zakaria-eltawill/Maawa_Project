<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthJwtTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_phone_and_region()
    {
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Osama Ellafi',
            'email' => 'osama@example.com',
            'password' => 'osama123',
            'password_confirmation' => 'osama123',
            'role' => 'owner',
            'phone_number' => '0912345678',
            'region' => 'Benghazi',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'phone_number', 'region', 'role'],
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
            ])
            ->assertJsonPath('user.phone_number', '0912345678')
            ->assertJsonPath('user.region', 'Benghazi')
            ->assertJsonPath('user.email', 'osama@example.com')
            ->assertJsonPath('user.role', 'owner');

        $this->assertDatabaseHas('users', [
            'email' => 'osama@example.com',
            'phone_number' => '0912345678',
            'region' => 'Benghazi',
            'role' => 'owner',
        ]);
    }

    /** @test */
    public function registration_requires_phone_number()
    {
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Ali Ahmed',
            'email' => 'ali@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'region' => 'Tripoli',
            // missing phone_number
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function registration_requires_region()
    {
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Ali Ahmed',
            'email' => 'ali@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
            'phone_number' => '0923456789',
            // missing region
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['region']);
    }

    /** @test */
    public function phone_number_must_be_unique()
    {
        // Create first user
        User::create([
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0912345678',
            'region' => 'Tripoli',
        ]);

        // Try to register with same phone number
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'owner',
            'phone_number' => '0912345678', // duplicate
            'region' => 'Benghazi',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function user_can_login_and_response_includes_phone_and_region()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'phone_number' => '0912345678',
            'region' => 'Tripoli',
        ]);

        // Login
        $response = $this->postJson('/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'phone_number', 'region', 'role'],
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
            ])
            ->assertJsonPath('user.phone_number', '0912345678')
            ->assertJsonPath('user.region', 'Tripoli');
    }

    /** @test */
    public function me_endpoint_returns_phone_and_region()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'phone_number' => '0912345678',
            'region' => 'Benghazi',
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Call /me endpoint with token
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/me');

        $response->assertOk()
            ->assertJsonPath('user.phone_number', '0912345678')
            ->assertJsonPath('user.region', 'Benghazi');
    }

    /** @test */
    public function password_confirmation_is_required()
    {
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            // missing password_confirmation
            'role' => 'tenant',
            'phone_number' => '0912345678',
            'region' => 'Tripoli',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function can_register_as_tenant_with_all_fields()
    {
        $response = $this->postJson('/v1/auth/register', [
            'name' => 'Ahmed Ali',
            'email' => 'ahmed@example.com',
            'password' => 'ahmed123',
            'password_confirmation' => 'ahmed123',
            'role' => 'tenant',
            'phone_number' => '0925555555',
            'region' => 'Misrata',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'tenant')
            ->assertJsonPath('user.region', 'Misrata');
    }
}

