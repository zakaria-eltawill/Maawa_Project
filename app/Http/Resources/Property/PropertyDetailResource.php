<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photos = $this->photos ?? [];
        $thumbnail = !empty($photos) ? $photos[0]['url'] ?? null : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'city' => $this->city,
            'type' => $this->type,
            'price' => (float) $this->price,
            'thumbnail' => $thumbnail,
            'avg_rating' => $this->reviews()->avg('rating'),
            'description' => $this->description,
            'amenities' => $this->amenities ?? [],
            'photos' => array_map(function ($photo, $index) {
                return [
                    'url' => $photo['url'] ?? $photo,
                    'position' => $index,
                ];
            }, $photos, array_keys($photos)),
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
            ],
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
            'reviews_count' => $this->reviews()->count(),
        ];
    }
}
