<?php

namespace App\Http\Controllers;

use App\Http\Resources\Property\PropertyDetailResource;
use App\Http\Resources\Property\PropertySummaryResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Tenant: full explore mode - see all properties
        if ($user->isTenant()) {
            $query = Property::query()->with(['owner:id,name', 'reviews']);
        }
        // Owner: only their own properties
        elseif ($user->isOwner()) {
            $query = Property::query()
                ->where('owner_id', $user->id)
                ->with(['owner:id,name', 'reviews']);
        }
        // Admin: see all properties with full management access
        elseif ($user->isAdmin()) {
            $query = Property::query()->with(['owner:id,name', 'reviews']);
        }
        // Unsupported roles
        else {
            abort(403, 'Access denied');
        }

        // Apply filters
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $properties = $query->paginate($perPage);

        return response()->json([
            'data' => PropertySummaryResource::collection($properties->items()),
            'next_cursor' => $properties->hasMorePages() ? $properties->nextPageUrl() : null,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $property = Property::with(['owner', 'reviews.tenant'])->findOrFail($id);
        return response()->json(new PropertyDetailResource($property));
    }
}
