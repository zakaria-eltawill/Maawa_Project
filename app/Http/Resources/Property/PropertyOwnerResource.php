<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array for owner view.
     * This includes all property details plus owner-specific information.
     */
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

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'city' => $this->city,
            'type' => $this->type,
            'price' => (float) $this->price,
            'thumbnail' => $thumbnail,
            'photos' => $normalizedPhotos,
            'amenities' => $this->amenities ?? [],
            'location' => [
                'latitude' => $this->location_lat,
                'longitude' => $this->location_lng,
                'map_url' => $this->location_url
                    ?: (($this->location_lat !== null && $this->location_lng !== null)
                        ? 'https://www.google.com/maps?q='.$this->location_lat.','.$this->location_lng
                        : null),
            ],
            'statistics' => [
                'bookings_count' => $this->bookings()->count(),
                'reviews_count' => $this->reviews()->count(),
                'avg_rating' => $this->reviews()->avg('rating'),
                'total_revenue' => (float) $this->bookings()
                    ->where('status', 'CONFIRMED')
                    ->sum('total'),
            ],
            'availability' => [
                'unavailable_dates' => $this->unavailable_dates ?? [],
            ],
            'version' => $this->version ?? 1,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

