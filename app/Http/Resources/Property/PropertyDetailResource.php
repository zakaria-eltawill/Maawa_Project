<?php

namespace App\Http\Resources\Property;

use Carbon\Carbon;
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

        // Calculate unavailable dates from bookings (ACCEPTED and CONFIRMED)
        $unavailableDates = $this->calculateUnavailableDates();

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
            'unavailable_dates' => $unavailableDates, // At root level for frontend
            'availability' => [
                'unavailable_dates' => $unavailableDates, // Keep for backward compatibility
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

    /**
     * Calculate unavailable dates from bookings and property settings
     * 
     * Includes:
     * - Dates from ACCEPTED bookings (check_in to check_out, exclusive)
     * - Dates from CONFIRMED bookings (check_in to check_out, exclusive)
     * - Dates explicitly blocked by owner (from property.unavailable_dates)
     * 
     * @return array Array of date strings in YYYY-MM-DD format
     */
    protected function calculateUnavailableDates(): array
    {
        $unavailableDates = [];
        
        // Get dates from ACCEPTED and CONFIRMED bookings
        // Use loaded relationship if available, otherwise query
        $bookings = $this->relationLoaded('bookings')
            ? $this->bookings->whereIn('status', ['ACCEPTED', 'CONFIRMED'])
            : $this->bookings()->whereIn('status', ['ACCEPTED', 'CONFIRMED'])->get(['check_in', 'check_out']);
        
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in);
            $checkOut = Carbon::parse($booking->check_out);
            
            // Generate all dates from check_in to check_out (exclusive)
            $currentDate = $checkIn->copy();
            while ($currentDate->lt($checkOut)) {
                $unavailableDates[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }
        }
        
        // Merge with property's explicitly blocked dates
        $propertyBlockedDates = $this->unavailable_dates ?? [];
        if (is_array($propertyBlockedDates)) {
            foreach ($propertyBlockedDates as $date) {
                // Ensure date is in YYYY-MM-DD format
                if (is_string($date)) {
                    try {
                        $formattedDate = Carbon::parse($date)->format('Y-m-d');
                        $unavailableDates[] = $formattedDate;
                    } catch (\Exception $e) {
                        // Skip invalid dates
                        continue;
                    }
                }
            }
        }
        
        // Remove duplicates and sort
        $unavailableDates = array_unique($unavailableDates);
        sort($unavailableDates);
        
        return array_values($unavailableDates);
    }
}
