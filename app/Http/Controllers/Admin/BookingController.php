<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingStatusNotification;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Cancel a booking (Admin only)
     * Only admins can cancel bookings
     * Can cancel bookings with status: PENDING, ACCEPTED, CONFIRMED
     */
    public function cancel(string $id, Request $request): JsonResponse
    {
        $booking = Booking::with(['property', 'tenant'])->findOrFail($id);

        // Check if booking can be canceled
        $cancelableStatuses = ['PENDING', 'ACCEPTED', 'CONFIRMED'];
        if (!in_array($booking->status, $cancelableStatuses)) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'This booking cannot be canceled. Only PENDING, ACCEPTED, or CONFIRMED bookings can be canceled.',
            ], 400);
        }

        // Update booking status to CANCELED
        $booking->update([
            'status' => 'CANCELED',
        ]);

        // Send notifications to tenant and owner
        SendBookingStatusNotification::dispatch($booking);

        return response()->json([
            'id' => $booking->id,
            'status' => 'CANCELED',
            'message' => 'Booking has been canceled successfully.',
        ], 200);
    }
}

