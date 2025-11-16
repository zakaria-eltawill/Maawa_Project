<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingOverlapTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cannot_double_book_same_dates()
    {
        // Create property and users
        $owner = User::create([
            'name' => 'Property Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'A test property',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 100.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'test-key-1'])
            ->assertCreated();

        // Second booking with exact same dates should FAIL
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'test-key-2'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function cannot_book_overlapping_dates_partial_overlap_start()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'villa',
            'price' => 150.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking: Dec 20-25
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-3'])
            ->assertCreated();

        // Second booking: Dec 22-27 (overlaps at the end of first booking)
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-22',
                'check_out' => '2025-12-27',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-4'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function cannot_book_overlapping_dates_partial_overlap_end()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 120.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking: Dec 20-25
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-5'])
            ->assertCreated();

        // Second booking: Dec 18-22 (overlaps at the start of first booking)
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-18',
                'check_out' => '2025-12-22',
                'guests' => 3,
            ], ['X-Idempotency-Key' => 'key-6'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function cannot_book_dates_that_completely_contain_existing_booking()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'chalet',
            'price' => 200.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking: Dec 22-24
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-22',
                'check_out' => '2025-12-24',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-7'])
            ->assertCreated();

        // Second booking: Dec 20-26 (completely contains first booking)
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-26',
                'guests' => 4,
            ], ['X-Idempotency-Key' => 'key-8'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function cannot_book_dates_that_are_completely_within_existing_booking()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 100.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking: Dec 20-30
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-30',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-9'])
            ->assertCreated();

        // Second booking: Dec 22-25 (completely within first booking)
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-22',
                'check_out' => '2025-12-25',
                'guests' => 1,
            ], ['X-Idempotency-Key' => 'key-10'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function can_book_consecutive_dates_without_overlap()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'villa',
            'price' => 150.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking: Dec 20-25
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-11'])
            ->assertCreated();

        // Second booking: Dec 25-30 (starts on same day first ends - should succeed)
        $response = $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-25',
                'check_out' => '2025-12-30',
                'guests' => 3,
            ], ['X-Idempotency-Key' => 'key-12']);

        $response->assertCreated();
    }

    /** @test */
    public function rejected_bookings_do_not_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 100.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Create a REJECTED booking manually
        Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'REJECTED',
        ]);

        // New booking on same dates should succeed because first was REJECTED
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-13'])
            ->assertCreated();
    }

    /** @test */
    public function canceled_bookings_do_not_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'chalet',
            'price' => 180.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Create a CANCELED booking manually
        Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 900.00,
            'status' => 'CANCELED',
        ]);

        // New booking on same dates should succeed because first was CANCELED
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 3,
            ], ['X-Idempotency-Key' => 'key-14'])
            ->assertCreated();
    }

    /** @test */
    public function expired_bookings_do_not_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'villa',
            'price' => 250.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Create an EXPIRED booking manually
        Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 1250.00,
            'status' => 'EXPIRED',
        ]);

        // New booking on same dates should succeed because first was EXPIRED
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 4,
            ], ['X-Idempotency-Key' => 'key-15'])
            ->assertCreated();
    }

    /** @test */
    public function pending_bookings_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 100.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // First booking (status will be PENDING)
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-16'])
            ->assertCreated();

        // Second booking should be blocked by PENDING status
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-22',
                'check_out' => '2025-12-27',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-17'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function accepted_bookings_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'villa',
            'price' => 150.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Create an ACCEPTED booking
        Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 750.00,
            'status' => 'ACCEPTED',
            'payment_due_at' => now()->addHours(24),
        ]);

        // New booking on overlapping dates should fail
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-22',
                'check_out' => '2025-12-27',
                'guests' => 3,
            ], ['X-Idempotency-Key' => 'key-18'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function confirmed_bookings_block_availability()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'chalet',
            'price' => 200.00,
            'thumbnail' => 'test.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Create a CONFIRMED booking
        Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 1000.00,
            'status' => 'CONFIRMED',
        ]);

        // New booking on overlapping dates should fail
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property->id,
                'check_in' => '2025-12-18',
                'check_out' => '2025-12-22',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-19'])
            ->assertStatus(409)
            ->assertJsonPath('detail', 'date_range_unavailable');
    }

    /** @test */
    public function can_book_different_properties_on_same_dates()
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property1 = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Property 1',
            'description' => 'Test',
            'city' => 'Tripoli',
            'type' => 'apartment',
            'price' => 100.00,
            'thumbnail' => 'test1.jpg',
            'latitude' => 32.8872,
            'longitude' => 13.1913,
        ]);

        $property2 = Property::create([
            'owner_id' => $owner->id,
            'title' => 'Property 2',
            'description' => 'Test',
            'city' => 'Benghazi',
            'type' => 'villa',
            'price' => 150.00,
            'thumbnail' => 'test2.jpg',
            'latitude' => 32.1167,
            'longitude' => 20.0833,
        ]);

        $tenant1 = User::create([
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant2 = User::create([
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Book property 1
        $this->actingAs($tenant1, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property1->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 2,
            ], ['X-Idempotency-Key' => 'key-20'])
            ->assertCreated();

        // Book property 2 on same dates - should succeed (different property)
        $this->actingAs($tenant2, 'api')
            ->postJson('/v1/bookings', [
                'property_id' => $property2->id,
                'check_in' => '2025-12-20',
                'check_out' => '2025-12-25',
                'guests' => 3,
            ], ['X-Idempotency-Key' => 'key-21'])
            ->assertCreated();
    }
}

