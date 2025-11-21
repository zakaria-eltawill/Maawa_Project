<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingStatusNotification;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['property', 'tenant'])
            ->orderBy('created_at', 'desc');

        // Filter by property title
        if ($request->filled('property')) {
            $query->whereHas('property', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->property . '%');
            });
        }

        // Filter by tenant name
        if ($request->filled('tenant')) {
            $query->whereHas('tenant', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->tenant . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by check-in date
        if ($request->filled('check_in')) {
            $query->whereDate('check_in', '>=', $request->check_in);
        }

        // Filter by check-out date
        if ($request->filled('check_out')) {
            $query->whereDate('check_out', '<=', $request->check_out);
        }

        $bookings = $query->paginate(20);
        
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(string $id)
    {
        $booking = Booking::with(['property', 'tenant'])->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking (Admin only)
     * Only admins can cancel bookings
     * Can cancel bookings with status: PENDING, ACCEPTED, CONFIRMED
     */
    public function cancel(string $id, Request $request)
    {
        $booking = Booking::with(['property', 'tenant'])->findOrFail($id);

        // Check if booking can be canceled
        $cancelableStatuses = ['PENDING', 'ACCEPTED', 'CONFIRMED'];
        if (!in_array($booking->status, $cancelableStatuses)) {
            return redirect()
                ->route('admin.bookings.show', $id)
                ->with('error', 'This booking cannot be canceled. Only PENDING, ACCEPTED, or CONFIRMED bookings can be canceled.');
        }

        // Update booking status to CANCELED
        $booking->update([
            'status' => 'CANCELED',
        ]);

        // Send notifications to tenant and owner
        SendBookingStatusNotification::dispatch($booking);

        return redirect()
            ->route('admin.bookings.show', $id)
            ->with('success', 'Booking has been canceled successfully.');
    }
}
