<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\MockPaymentRequest;
use App\Jobs\SendBookingStatusNotification;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function mock(MockPaymentRequest $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        // Accept both ACCEPTED and CONFIRMED statuses
        if (!in_array($booking->status, ['ACCEPTED', 'CONFIRMED'])) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Gone',
                'status' => 410,
                'detail' => 'Booking is not in ACCEPTED or CONFIRMED status',
            ], 410);
        }

        // Simulate payment processing - in real scenario, this would call payment gateway
        // For now, we'll check if there's a 'fail' parameter to simulate failure
        $shouldFail = $request->has('fail') && $request->boolean('fail');

        if ($shouldFail) {
            // Payment failed
            $booking->update(['status' => 'FAILED', 'is_paid' => false]);

            // Dispatch notification job
            SendBookingStatusNotification::dispatch($booking);

            return response()->json([
                'type' => 'about:blank',
                'title' => 'Payment Failed',
                'status' => 402,
                'detail' => 'Payment processing failed',
                'booking_id' => $booking->id,
                'status' => 'FAILED',
            ], 402);
        }

        // Payment successful - update status to CONFIRMED and set is_paid to true
        $booking->update([
            'status' => 'CONFIRMED',
            'is_paid' => true,
        ]);

        // Dispatch notification job
        SendBookingStatusNotification::dispatch($booking);

        return response()->json([
            'booking_id' => $booking->id,
            'status' => 'CONFIRMED',
            'is_paid' => true,
            'receipt_no' => 'MOCK-' . strtoupper(substr(md5($booking->id . now()), 0, 12)),
            'paid_at' => now()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
