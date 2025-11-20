<?php

namespace App\Http\Controllers;

use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\Property\PropertyDetailResource;
use App\Http\Resources\Property\PropertyOwnerResource;
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
            $query = Property::query()->with(['owner:id,name,email,phone_number,region', 'reviews']);
        }
        // Owner: only their own properties
        elseif ($user->isOwner()) {
            $query = Property::query()
                ->where('owner_id', $user->id)
                ->with(['owner:id,name,email,phone_number,region', 'reviews']);
        }
        // Admin: see all properties with full management access
        elseif ($user->isAdmin()) {
            $query = Property::query()->with(['owner:id,name,email,phone_number,region', 'reviews']);
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

    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::with(['owner:id,name,email,phone_number,region', 'reviews.tenant', 'bookings'])->findOrFail($id);

        // If user is the owner, return owner-specific resource
        if ($user->isOwner() && $property->owner_id === $user->id) {
            return response()->json(new \App\Http\Resources\Property\PropertyOwnerResource($property));
        }

        // For tenants and admins, return regular detail resource
        return response()->json(new PropertyDetailResource($property));
    }

    /**
     * Get owner's properties (owner-specific endpoint)
     */
    public function ownerProperties(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only owners can access this endpoint
        if (!$user->isOwner()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can access this endpoint',
            ], 403);
        }

        $query = Property::query()
            ->where('owner_id', $user->id)
            ->with(['owner:id,name,email,phone_number,region', 'reviews', 'bookings']);

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
        $properties = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => \App\Http\Resources\Property\PropertyOwnerResource::collection($properties->items()),
            'next_cursor' => $properties->hasMorePages() ? $properties->nextPageUrl() : null,
        ]);
    }

    /**
     * Get owner's property by ID (owner-specific endpoint)
     */
    public function ownerProperty(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only owners can access this endpoint
        if (!$user->isOwner()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can access this endpoint',
            ], 403);
        }

        $property = Property::with(['owner:id,name,email,phone_number,region', 'reviews', 'bookings'])
            ->where('owner_id', $user->id)
            ->findOrFail($id);

        return response()->json(new \App\Http\Resources\Property\PropertyOwnerResource($property));
    }

    /**
     * Create edit proposal for owner's property
     * This creates a PENDING proposal that requires admin approval
     */
    public function createEditProposal(string $id, UpdatePropertyRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Only owners can access this endpoint
        if (!$user->isOwner()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Only owners can access this endpoint',
            ], 403);
        }

        $property = Property::where('owner_id', $user->id)->findOrFail($id);

        // Get validated data
        $payload = $request->validated();

        // Handle location if provided - convert to proposal format
        if (isset($payload['location'])) {
            $location = [];
            if (isset($payload['location']['latitude'])) {
                $location['latitude'] = $payload['location']['latitude'];
            }
            if (isset($payload['location']['longitude'])) {
                $location['longitude'] = $payload['location']['longitude'];
            }
            if (isset($payload['location']['map_url'])) {
                $location['map_url'] = $payload['location']['map_url'];
            }
            $payload['location'] = $location;
        }

        // Create EDIT proposal
        $proposal = \App\Models\Proposal::create([
            'owner_id' => $user->id,
            'property_id' => $property->id,
            'type' => 'EDIT',
            'status' => 'PENDING',
            'version' => $property->version ?? 1,
            'payload' => $payload,
        ]);

        // Notify admins
        $this->notifyAdminsOfProposal($proposal, $user->name);

        return response()->json([
            'id' => $proposal->id,
            'status' => 'PENDING',
            'message' => 'Edit proposal created. Waiting for admin approval.',
        ], 201);
    }

    /**
     * Notify admins of proposal (helper method)
     */
    protected function notifyAdminsOfProposal(\App\Models\Proposal $proposal, string $ownerName): void
    {
        $typeKey = strtolower($proposal->type);
        $titleKey = "admin.notification_templates.proposal_{$typeKey}_title";
        $bodyKey = "admin.notification_templates.proposal_{$typeKey}_body";

        $params = [
            'owner' => $ownerName,
        ];

        $propertyId = $proposal->property_id;
        if ($propertyId) {
            $params['property'] = $propertyId;
        }

        \App\Models\AdminNotification::create([
            'type' => "proposal.{$typeKey}",
            'title' => __($titleKey, $params, 'en'),
            'message' => __($bodyKey, $params, 'en'),
            'entity_type' => \App\Models\Proposal::class,
            'entity_id' => $proposal->id,
            'data' => [
                'title_key' => $titleKey,
                'body_key' => $bodyKey,
                'params' => $params,
                'proposal_id' => $proposal->id,
                'proposal_type' => $proposal->type,
                'owner_id' => $proposal->owner_id,
                'route' => route('admin.proposals.show', $proposal->id),
            ],
        ]);
    }
}
