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
        $query = Property::query()->with(['owner', 'reviews']);

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
