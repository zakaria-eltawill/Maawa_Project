<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySummaryResource extends JsonResource
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

        return [
            'id' => $this->id,
            'title' => $this->title,
            'city' => $this->city,
            'type' => $this->type,
            'price' => (float) $this->price,
            'thumbnail' => $thumbnail,
            'avg_rating' => $this->reviews()->avg('rating'),
        ];
    }
}
