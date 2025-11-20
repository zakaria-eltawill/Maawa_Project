<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photos = $this->photos ?? [];
        
        // Handle photos as both string URLs and array format
        $thumbnail = null;
        if (!empty($photos)) {
            $firstPhoto = $photos[0];
            if (is_array($firstPhoto)) {
                $thumbnail = $firstPhoto['url'] ?? null;
            } else {
                $thumbnail = $firstPhoto; // It's already a string URL
            }
        }

        // Normalize photos array - handle both string URLs and array format
        $normalizedPhotos = array_map(function ($photo, $index) {
            if (is_array($photo)) {
                return [
                    'url' => $photo['url'] ?? null,
                    'position' => $index,
                ];
            } else {
                return [
                    'url' => $photo, // It's already a string URL
                    'position' => $index,
                ];
            }
        }, $photos, array_keys($photos));

        // Calculate rating - ensure it's always a float (0.0 if no reviews)
        $avgRating = $this->reviews()->avg('rating');
        $avgRating = $avgRating !== null ? (float) $avgRating : 0.0;
        
        // Get reviews count
        $reviewsCount = $this->reviews()->count();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'city' => $this->city,
            'type' => $this->type,
            'price' => (float) $this->price,
            'thumbnail' => $thumbnail,
            'avg_rating' => $avgRating,
            'average_rating' => $avgRating, // Alias for frontend compatibility
            'description' => $this->description,
            'amenities' => $this->amenities ?? [],
            'photos' => $normalizedPhotos,
            'owner' => $this->when($this->owner, function () {
                return [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'phone_number' => $this->owner->phone_number,
                    'email' => $this->owner->email,
                    'region' => $this->owner->region,
                ];
            }, null),
            'availability' => [
                'unavailable_dates' => $this->unavailable_dates ?? [],
            ],
            'location' => [
                'latitude' => $this->location_lat,
                'longitude' => $this->location_lng,
                'map_url' => $this->location_url
                    ?: (($this->location_lat !== null && $this->location_lng !== null)
                        ? 'https://www.google.com/maps?q='.$this->location_lat.','.$this->location_lng
                        : null),
            ],
            'reviews_count' => $reviewsCount,
        ];
    }
}
