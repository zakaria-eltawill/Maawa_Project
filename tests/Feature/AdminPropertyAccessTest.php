<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminPropertyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_properties()
    {
        // Create admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        // Create two owners
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

        // Admin calls /properties
        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/properties');

        $response->assertOk();

        $data = $response->json('data');
        
        // Should see all 3 properties
        $this->assertCount(3, $data);
        
        $returnedIds = collect($data)->pluck('id')->toArray();
        $this->assertContains($property1->id, $returnedIds);
        $this->assertContains($property2->id, $returnedIds);
        $this->assertContains($property3->id, $returnedIds);
    }

    public function test_admin_can_see_properties_with_full_information()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone_number' => '0911111111',
            'region' => 'Tripoli',
        ]);

        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Property Owner',
            'phone_number' => '0922222222',
            'region' => 'Benghazi',
        ]);

        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Test Property',
            'type' => 'apartment',
            'city' => 'Tripoli',
            'price' => 250.00,
        ]);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/v1/properties');

        $response->assertOk();

        $data = $response->json('data');
        $propertyData = collect($data)->firstWhere('id', $property->id);
        
        $this->assertNotNull($propertyData);
        $this->assertEquals('Test Property', $propertyData['title']);
        $this->assertEquals('apartment', $propertyData['type']);
        $this->assertEquals('Tripoli', $propertyData['city']);
        $this->assertEquals(250.00, $propertyData['price']);
    }
}

