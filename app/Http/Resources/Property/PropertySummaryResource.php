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
            'reviews_count' => $reviewsCount,
        ];
    }
}
