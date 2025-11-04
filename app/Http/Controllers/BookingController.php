<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\OwnerDecisionRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // This should never be null due to middleware, but add safety check
        if (!$user) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Unauthenticated',
            ], 401);
        }
        
        $query = Booking::with(['property', 'tenant']);

        if ($user->role === 'tenant') {
            $query->where('tenant_id', $user->id);
        } elseif ($user->role === 'owner') {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => BookingResource::collection($bookings->items()),
            'next_cursor' => $bookings->hasMorePages() ? $bookings->nextPageUrl() : null,
        ]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $property = Property::findOrFail($request->property_id);
        
        // Calculate total (simple: price * nights)
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);
        $total = $property->price * $nights;

        $booking = Booking::create([
            'property_id' => $request->property_id,
            'tenant_id' => auth()->id(),
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'guests' => $request->guests,
            'total' => $total,
            'status' => 'PENDING',
        ]);

        return response()->json([
            'id' => $booking->id,
            'status' => 'PENDING',
            'total' => (float) $total,
            'payment_window' => null,
        ], 201);
    }

    public function ownerDecision(string $id, OwnerDecisionRequest $request): JsonResponse
    {
        $booking = Booking::with('property')->findOrFail($id);
        $user = auth()->user();

        if ($booking->property->owner_id !== $user->id) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Not authorized',
            ], 403);
        }

        if ($booking->status !== 'PENDING') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Conflict',
                'status' => 409,
                'detail' => 'Booking status cannot be changed',
            ], 409);
        }

        $decision = $request->decision === 'ACCEPT' ? 'ACCEPTED' : 'REJECTED';
        $paymentDueAt = $decision === 'ACCEPTED' ? now()->addHours(24) : null;

        $booking->update([
            'status' => $decision,
            'payment_due_at' => $paymentDueAt,
        ]);

        return response()->json([
            'id' => $booking->id,
            'status' => $decision,
            'payment_due_at' => $paymentDueAt?->format('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
