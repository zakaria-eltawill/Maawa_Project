<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(string $id, Request $request): JsonResponse
    {
        $property = Property::findOrFail($id);
        
        $perPage = min($request->get('per_page', 20), 50);
        $reviews = $property->reviews()->with('tenant')->paginate($perPage);

        return response()->json([
            'data' => ReviewResource::collection($reviews->items()),
            'next_cursor' => $reviews->hasMorePages() ? $reviews->nextPageUrl() : null,
        ]);
    }

    public function store(string $id, StoreReviewRequest $request): JsonResponse
    {
        $property = Property::findOrFail($id);
        $user = auth()->user();

        // Find a COMPLETED booking for this property and tenant
        $booking = Booking::where('property_id', $property->id)
            ->where('tenant_id', $user->id)
            ->where('status', 'COMPLETED')
            ->whereDoesntHave('review')
            ->first();

        if (!$booking) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'No completed booking found for review',
            ], 403);
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'property_id' => $property->id,
            'tenant_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(new ReviewResource($review), 201);
    }
}
