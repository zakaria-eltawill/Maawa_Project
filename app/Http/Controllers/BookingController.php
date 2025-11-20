<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\OwnerDecisionRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Models\Booking;
use App\Models\Property;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {
    }
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

        // Tenant: only their bookings
            if ($user->isTenant()) {
                $query = Booking::where('tenant_id', $user->id)
                    ->with(['property:id,title,type,owner_id', 'tenant:id,name,email,phone_number,region']);
        }
        // Owner: all bookings on their properties
        elseif ($user->isOwner()) {
            $query = Booking::whereHas('property', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                })
                ->with([
                    'property:id,title,type,owner_id',
                    'tenant:id,name,email,phone_number,region'
                ]);
        }
        // Admin: see all bookings with full information
        elseif ($user->isAdmin()) {
            $query = Booking::query()
                ->with([
                    'property:id,title,type,owner_id,city,price,thumbnail',
                    'tenant:id,name,email,phone_number,region'
                ]);
        }
        // Unsupported roles
        else {
            abort(403, 'Access denied');
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        // Order by newest first
        $query->orderByDesc('created_at');

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
        
        // Check availability first (throws ConflictException if unavailable)
        $this->availabilityService->ensureAvailable(
            $request->property_id,
            $request->check_in,
            $request->check_out
        );
        
        // Use transaction to ensure atomicity and prevent race conditions
        $booking = DB::transaction(function () use ($request, $property) {
            // Double-check availability within transaction for race condition protection
            $this->availabilityService->ensureAvailable(
                $request->property_id,
                $request->check_in,
                $request->check_out
            );
            
            // Calculate total (simple: price * nights)
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);
            $nights = $checkIn->diffInDays($checkOut);
            $total = $property->price * $nights;

            return Booking::create([
                'property_id' => $request->property_id,
                'tenant_id' => auth()->id(),
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'guests' => $request->guests,
                'total' => $total,
                'status' => 'PENDING',
            ]);
        });

        return response()->json([
            'id' => $booking->id,
            'status' => 'PENDING',
            'total' => (float) $booking->total,
            'payment_window' => null,
        ], 201);
    }

    public function ownerBookings(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Only owners can access this endpoint
        if (!$user->isOwner()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can access this endpoint',
            ], 403);
        }

        // Get all bookings on owner's properties
        $query = Booking::whereHas('property', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })
            ->with([
                'property:id,title,type,owner_id',
                'tenant:id,name,email,phone_number,region'
            ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        // Order by newest first
        $query->orderByDesc('created_at');

        $perPage = min($request->get('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => BookingResource::collection($bookings->items()),
            'next_cursor' => $bookings->hasMorePages() ? $bookings->nextPageUrl() : null,
        ]);
    }

    public function tenantBookings(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Only tenants can access this endpoint
        if (!$user->isTenant()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only tenants can access this endpoint',
            ], 403);
        }

        // Get all bookings created by the tenant
        $query = Booking::where('tenant_id', $user->id)
            ->with(['property:id,title,type,owner_id', 'tenant:id,name,email,phone_number,region']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        // Order by newest first
        $query->orderByDesc('created_at');

        $perPage = min($request->get('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => BookingResource::collection($bookings->items()),
            'next_cursor' => $bookings->hasMorePages() ? $bookings->nextPageUrl() : null,
        ]);
    }

    /**
     * Get owner bookings by status
     */
    public function ownerBookingsByStatus(string $status, Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Only owners can access this endpoint
        if (!$user->isOwner()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can access this endpoint',
            ], 403);
        }

        // Validate status
        $validStatuses = ['PENDING', 'ACCEPTED', 'CONFIRMED', 'REJECTED', 'CANCELED', 'EXPIRED', 'COMPLETED', 'FAILED'];
        $status = strtoupper($status);
        
        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Invalid booking status',
            ], 400);
        }

        // Get bookings on owner's properties with specific status
        $query = Booking::whereHas('property', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })
            ->where('status', $status)
            ->with([
                'property:id,title,type,owner_id',
                'tenant:id,name,email,phone_number,region'
            ]);

        // Apply date filters
        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        // Order by newest first
        $query->orderByDesc('created_at');

        $perPage = min($request->get('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => BookingResource::collection($bookings->items()),
            'next_cursor' => $bookings->hasMorePages() ? $bookings->nextPageUrl() : null,
        ]);
    }

    /**
     * Get tenant bookings by status
     */
    public function tenantBookingsByStatus(string $status, Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Only tenants can access this endpoint
        if (!$user->isTenant()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only tenants can access this endpoint',
            ], 403);
        }

        // Validate status
        $validStatuses = ['PENDING', 'ACCEPTED', 'CONFIRMED', 'REJECTED', 'CANCELED', 'EXPIRED', 'COMPLETED', 'FAILED'];
        $status = strtoupper($status);
        
        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Invalid booking status',
            ], 400);
        }

        // Get tenant's bookings with specific status
        $query = Booking::where('tenant_id', $user->id)
            ->where('status', $status)
            ->with(['property:id,title,type,owner_id', 'tenant:id,name,email,phone_number,region']);

        // Apply date filters
        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->to);
        }

        // Order by newest first
        $query->orderByDesc('created_at');

        $perPage = min($request->get('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        return response()->json([
            'data' => BookingResource::collection($bookings->items()),
            'next_cursor' => $bookings->hasMorePages() ? $bookings->nextPageUrl() : null,
        ]);
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
