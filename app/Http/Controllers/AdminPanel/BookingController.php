<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['property', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(string $id)
    {
        $booking = Booking::with(['property', 'tenant'])->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }
}
