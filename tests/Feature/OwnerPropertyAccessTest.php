<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OwnerPropertyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_only_see_their_own_properties()
    {
        // Create two owners
        $owner1 = User::factory()->create([
            'role' => 'owner',
            'name' => 'Owner 1',
            'email' => 'owner1@example.com',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $owner2 = User::factory()->create([
            'role' => 'owner',
            'name' => 'Owner 2',
            'email' => 'owner2@example.com',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        // Owner1 has 2 properties
        $property1 = Property::factory()->create([
            'owner_id' => $owner1->id,
            'title' => 'Owner 1 Property 1',
        ]);

        $property2 = Property::factory()->create([
            'owner_id' => $owner1->id,
            'title' => 'Owner 1 Property 2',
        ]);

        // Owner2 has 1 property
        $property3 = Property::factory()->create([
            'owner_id' => $owner2->id,
            'title' => 'Owner 2 Property 1',
        ]);

        // Owner1 calls /properties
        $token = JWTAuth::fromUser($owner1);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/properties');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should contain exactly 2 properties (Owner1's properties)
        $this->assertCount(2, $data);
        
        // Verify all returned properties belong to Owner1
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($property1->id, $returnedIds);
        $this->assertContains($property2->id, $returnedIds);
        $this->assertNotContains($property3->id, $returnedIds);
    }

    public function test_tenant_can_see_all_properties()
    {
        // Create owner and tenant
        $owner = User::factory()->create([
            'role' => 'owner',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $tenant = User::factory()->create([
            'role' => 'tenant',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        // Owner has 2 properties
        $property1 = Property::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Property 1',
        ]);

        $property2 = Property::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Property 2',
        ]);

        // Tenant calls /properties
        $token = JWTAuth::fromUser($tenant);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/properties');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should see all properties (explore mode)
        $this->assertCount(2, $data);
        
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($property1->id, $returnedIds);
        $this->assertContains($property2->id, $returnedIds);
    }

    public function test_owner_cannot_see_other_owners_properties()
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

        // Owner2 has a property
        $owner2Property = Property::factory()->create([
            'owner_id' => $owner2->id,
            'title' => 'Owner 2 Property',
        ]);

        // Owner1 calls /properties
        $token = JWTAuth::fromUser($owner1);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/properties');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should be empty (Owner1 has no properties)
        $this->assertCount(0, $data);
        
        // Owner2's property should not be in the list
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertNotContains($owner2Property->id, $returnedIds);
    }
}

