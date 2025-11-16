<?php

namespace App\Services\Booking;

use App\Exceptions\ConflictException;
use App\Models\Booking;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Ensure the property is available for the given date range.
     * 
     * Throws ConflictException if there are any overlapping bookings.
     * 
     * Overlap rule: A new booking [A,B] overlaps existing [X,Y] when: A < Y AND B > X
     * 
     * Only considers bookings with status: PENDING, ACCEPTED, CONFIRMED
     * Excludes: REJECTED, CANCELED, EXPIRED, COMPLETED, FAILED
     * 
     * @param string $propertyId
     * @param string $checkIn (Y-m-d format)
     * @param string $checkOut (Y-m-d format)
     * @throws ConflictException
     */
    public function ensureAvailable(string $propertyId, string $checkIn, string $checkOut): void
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Query for overlapping bookings
        // A new booking [checkIn, checkOut] overlaps existing [check_in, check_out] when:
        // checkIn < check_out AND checkOut > check_in
        $exists = Booking::where('property_id', $propertyId)
            ->whereIn('status', ['PENDING', 'ACCEPTED', 'CONFIRMED'])
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                // Standard interval overlap: [A,B] overlaps [X,Y] if A < Y AND B > X
                $query->where('check_in', '<', $checkOutDate)
                      ->where('check_out', '>', $checkInDate);
            })
            ->exists();

        if ($exists) {
            throw new ConflictException('date_range_unavailable');
        }
    }
}

