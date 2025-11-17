<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OwnerBookingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_all_bookings_on_their_properties()
    {
        // Create owner and property
        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Property Owner',
            'email' => 'owner@example.com',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Beautiful Apartment',
            'type' => 'apartment',
        ]);

        // Create two tenants
        $tenantA = User::factory()->create([
            'role' => 'tenant',
            'name' => 'Tenant A',
            'email' => 'tenanta@example.com',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenantB = User::factory()->create([
            'role' => 'tenant',
            'name' => 'Tenant B',
            'email' => 'tenantb@example.com',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Tenant A makes a booking on the property
        $booking1 = Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenantA->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'PENDING',
        ]);

        // Tenant B makes another booking on the same property
        $booking2 = Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenantB->id,
            'check_in' => '2025-12-26',
            'check_out' => '2025-12-30',
            'guests' => 3,
            'total' => 600.00,
            'status' => 'ACCEPTED',
        ]);

        // Owner calls /bookings
        $token = JWTAuth::fromUser($owner);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/bookings');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should contain both bookings
        $this->assertCount(2, $data);
        
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($booking1->id, $returnedIds);
        $this->assertContains($booking2->id, $returnedIds);

        // Verify booking includes tenant details
        $booking1Data = collect($data)->firstWhere('id', $booking1->id);
        $this->assertNotNull($booking1Data);
        $this->assertArrayHasKey('tenant', $booking1Data);
        $this->assertEquals('Tenant A', $booking1Data['tenant']['name']);
        $this->assertEquals('0922222222', $booking1Data['tenant']['phone_number']);

        // Verify booking includes property details
        $this->assertArrayHasKey('property', $booking1Data);
        $this->assertEquals('Beautiful Apartment', $booking1Data['property']['title']);
        $this->assertEquals('apartment', $booking1Data['property']['type']);
    }

    public function test_tenant_sees_only_their_own_bookings()
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $property = Property::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $tenantA = User::factory()->create([
            'role' => 'tenant',
            'name' => 'Tenant A',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenantB = User::factory()->create([
            'role' => 'tenant',
            'name' => 'Tenant B',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Tenant A makes a booking
        $bookingA = Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenantA->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'PENDING',
        ]);

        // Tenant B makes a booking
        $bookingB = Booking::create([
            'property_id' => $property->id,
            'tenant_id' => $tenantB->id,
            'check_in' => '2025-12-26',
            'check_out' => '2025-12-30',
            'guests' => 3,
            'total' => 600.00,
            'status' => 'PENDING',
        ]);

        // Tenant A calls /bookings
        $token = JWTAuth::fromUser($tenantA);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/bookings');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should contain only Tenant A's booking
        $this->assertCount(1, $data);
        
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($bookingA->id, $returnedIds);
        $this->assertNotContains($bookingB->id, $returnedIds);
    }

    public function test_owner_does_not_see_bookings_on_other_owners_properties()
    {
        $owner1 = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $owner2 = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $tenant = User::factory()->create([
            'role' => 'tenant',
            'phone_number' => '0933333333',
            'region' => 'Misrata',
        ]);

        // Owner1 has a property
        $property1 = Property::factory()->create([
            'owner_id' => $owner1->id,
        ]);

        // Owner2 has a property
        $property2 = Property::factory()->create([
            'owner_id' => $owner2->id,
        ]);

        // Tenant makes booking on Owner2's property
        $bookingOnOwner2Property = Booking::create([
            'property_id' => $property2->id,
            'tenant_id' => $tenant->id,
            'check_in' => '2025-12-20',
            'check_out' => '2025-12-25',
            'guests' => 2,
            'total' => 500.00,
            'status' => 'PENDING',
        ]);

        // Owner1 calls /bookings
        $token = JWTAuth::fromUser($owner1);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/bookings');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should be empty (no bookings on Owner1's properties)
        $this->assertCount(0, $data);
        
        // Booking on Owner2's property should not appear
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertNotContains($bookingOnOwner2Property->id, $returnedIds);
    }
}

