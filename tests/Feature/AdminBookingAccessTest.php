<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminBookingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_bookings()
    {
        // Create admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        // Create owners and properties
        $owner1 = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $owner2 = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        $property1 = Property::factory()->create([
            'owner_id' => $owner1->id,
        ]);

        $property2 = Property::factory()->create([
            'owner_id' => $owner2->id,
        ]);

        // Create tenants
        $tenant1 = User::factory()->create([
            'role' => 'tenant',
            'phone_number' => '0944444444',
            'region' => 'Tripoli',
        ]);

        $tenant2 = User::factory()->create([
            'role' => 'tenant',
            'phone_number' => '0955555555',
            'region' => 'Benghazi',
        ]);

        // Create bookings across different properties
        $booking1 = Booking::create([
            'property_id' => $property1->id,
            'tenant_id' => $tenant1->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'PENDING',
        ]);

        $booking2 = Booking::create([
            'property_id' => $property2->id,
            'tenant_id' => $tenant2->id,
            'check_in' => '2025-12-26',
            'check_out' => '2025-12-30',
            'guests' => 3,
            'total' => 600.00,
            'status' => 'ACCEPTED',
        ]);

        // Admin calls /bookings
        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/bookings');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should see all bookings
        $this->assertCount(2, $data);
        
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($booking1->id, $returnedIds);
        $this->assertContains($booking2->id, $returnedIds);
    }

    public function test_admin_sees_full_booking_information()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $owner = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant = User::factory()->create([
            'role' => 'tenant',
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Beautiful Apartment',
            'type' => 'apartment',
            'city' => 'Tripoli',
            'price' => 250.00,
        ]);

        $booking = Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'PENDING',
        ]);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/bookings');

        $response->assertOk();

        $data = $response->json('data');
        $bookingData = collect($data)->firstWhere('id', $booking->id);
        
        $this->assertNotNull($bookingData);
        
        // Verify booking details
        $this->assertEquals('2025-12-20', $bookingData['check_in']);
        $this->assertEquals('2025-12-25', $bookingData['check_out']);
        $this->assertEquals(2, $bookingData['guests']);
        $this->assertEquals(500.00, $bookingData['total']);
        $this->assertEquals('PENDING', $bookingData['status']);
        
        // Verify property details (full information)
        $this->assertArrayHasKey('property', $bookingData);
        $this->assertEquals('Beautiful Apartment', $bookingData['property']['title']);
        $this->assertEquals('apartment', $bookingData['property']['type']);
        $this->assertEquals('Tripoli', $bookingData['property']['city']);
        $this->assertEquals(250.00, $bookingData['property']['price']);
        
        // Verify tenant details (full information)
        $this->assertArrayHasKey('tenant', $bookingData);
        $this->assertEquals('Test Tenant', $bookingData['tenant']['name']);
        $this->assertEquals('tenant@test.com', $bookingData['tenant']['email']);
        $this->assertEquals('0933333333', $bookingData['tenant']['phone_number']);
        $this->assertEquals('Misrata', $bookingData['tenant']['region']);
    }
}

