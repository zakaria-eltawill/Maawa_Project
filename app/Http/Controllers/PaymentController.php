<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\MockPaymentRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function mock(MockPaymentRequest $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status !== 'ACCEPTED') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Gone',
                'status' => 410,
                'detail' => 'Booking is not in ACCEPTED status',
            ], 410);
        }

        $booking->update(['status' => 'CONFIRMED']);

        return response()->json([
            'booking_id' => $booking->id,
            'status' => 'CONFIRMED',
            'receipt_no' => 'MOCK-' . strtoupper(substr(md5($booking->id . now()), 0, 12)),
            'paid_at' => now()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
